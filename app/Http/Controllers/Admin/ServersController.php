<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2016 Dane Everitt <dane@daneeveritt.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Pterodactyl\Http\Controllers\Admin;

use DB;
use Log;
use Alert;
use Pterodactyl\Models;
use Illuminate\Http\Request;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Repositories\ServerRepository;
use Pterodactyl\Repositories\DatabaseRepository;
use Pterodactyl\Exceptions\DisplayValidationException;

class ServersController extends Controller
{
    /**
     * Controller Constructor.
     */
    public function __construct()
    {
        //
    }

    public function getIndex(Request $request)
    {
        $query = Models\Server::withTrashed()->select(
            'servers.*',
            'nodes.name as a_nodeName',
            'users.email as a_ownerEmail',
            'allocations.ip',
            'allocations.port',
            'allocations.ip_alias'
        )->join('nodes', 'servers.node', '=', 'nodes.id')
        ->join('users', 'servers.owner', '=', 'users.id')
        ->join('allocations', 'servers.allocation', '=', 'allocations.id');

        if ($request->input('filter') && ! is_null($request->input('filter'))) {
            preg_match_all('/[^\s"\']+|"([^"]*)"|\'([^\']*)\'/', urldecode($request->input('filter')), $matches);
            foreach ($matches[0] as $match) {
                $match = str_replace('"', '', $match);
                if (strpos($match, ':')) {
                    list($field, $term) = explode(':', $match);
                    if ($field === 'node') {
                        $field = 'nodes.name';
                    } elseif ($field === 'owner') {
                        $field = 'users.email';
                    } elseif (! strpos($field, '.')) {
                        $field = 'servers.' . $field;
                    }

                    $query->orWhere($field, 'LIKE', '%' . $term . '%');
                } else {
                    $query->where('servers.name', 'LIKE', '%' . $match . '%');
                    $query->orWhere([
                        ['servers.username', 'LIKE', '%' . $match . '%'],
                        ['users.email', 'LIKE', '%' . $match . '%'],
                        ['allocations.port', 'LIKE', '%' . $match . '%'],
                        ['allocations.ip', 'LIKE', '%' . $match . '%'],
                    ]);
                }
            }
        }

        try {
            $servers = $query->paginate(20);
        } catch (\Exception $ex) {
            Alert::warning('There was an error with the search parameters provided.');
            $servers = Models\Server::withTrashed()->select(
                'servers.*',
                'nodes.name as a_nodeName',
                'users.email as a_ownerEmail',
                'allocations.ip',
                'allocations.port',
                'allocations.ip_alias'
            )->join('nodes', 'servers.node', '=', 'nodes.id')
            ->join('users', 'servers.owner', '=', 'users.id')
            ->join('allocations', 'servers.allocation', '=', 'allocations.id')
            ->paginate(20);
        }

        return view('admin.servers.index', [
            'servers' => $servers,
        ]);
    }

    public function getNew(Request $request)
    {
        return view('admin.servers.new', [
            'locations' => Models\Location::all(),
            'services' => Models\Service::all(),
        ]);
    }

    public function getView(Request $request, $id)
    {
        $server = Models\Server::withTrashed()->select(
            'servers.*',
            'users.email as a_ownerEmail',
            'services.name as a_serviceName',
            DB::raw('IFNULL(service_options.executable, services.executable) as a_serviceExecutable'),
            'service_options.docker_image',
            'service_options.name as a_servceOptionName',
            'allocations.ip',
            'allocations.port',
            'allocations.ip_alias'
        )->join('nodes', 'servers.node', '=', 'nodes.id')
        ->join('users', 'servers.owner', '=', 'users.id')
        ->join('services', 'servers.service', '=', 'services.id')
        ->join('service_options', 'servers.option', '=', 'service_options.id')
        ->join('allocations', 'servers.allocation', '=', 'allocations.id')
        ->where('servers.id', $id)
        ->first();

        if (! $server) {
            return abort(404);
        }

        return view('admin.servers.view', [
            'server' => $server,
            'node' => Models\Node::select(
                    'nodes.*',
                    'locations.long as a_locationName'
                )->join('locations', 'nodes.location', '=', 'locations.id')
                ->where('nodes.id', $server->node)
                ->first(),
            'assigned' => Models\Allocation::where('assigned_to', $id)->orderBy('ip', 'asc')->orderBy('port', 'asc')->get(),
            'unassigned' => Models\Allocation::where('node', $server->node)->whereNull('assigned_to')->orderBy('ip', 'asc')->orderBy('port', 'asc')->get(),
            'startup' => Models\ServiceVariables::select('service_variables.*', 'server_variables.variable_value as a_serverValue')
                ->join('server_variables', 'server_variables.variable_id', '=', 'service_variables.id')
                ->where('service_variables.option_id', $server->option)
                ->where('server_variables.server_id', $server->id)
                ->get(),
            'databases' => Models\Database::select('databases.*', 'database_servers.host as a_host', 'database_servers.port as a_port')
                ->where('server_id', $server->id)
                ->join('database_servers', 'database_servers.id', '=', 'databases.db_server')
                ->get(),
            'db_servers' => Models\DatabaseServer::all(),
        ]);
    }

    public function postNewServer(Request $request)
    {
        try {
            $server = new ServerRepository;
            $response = $server->create($request->all());

            return redirect()->route('admin.servers.view', ['id' => $response]);
        } catch (DisplayValidationException $ex) {
            return redirect()->route('admin.servers.new')->withErrors(json_decode($ex->getMessage()))->withInput();
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();

            return redirect()->route('admin.servers.new')->withInput();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled exception occured while attemping to add this server. Please try again.')->flash();

            return redirect()->route('admin.servers.new')->withInput();
        }
    }

    /**
     * Returns a JSON tree of all avaliable nodes in a given location.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function postNewServerGetNodes(Request $request)
    {
        if (! $request->input('location')) {
            return response()->json([
                'error' => 'Missing location in request.',
            ], 500);
        }

        return response()->json(Models\Node::select('id', 'name', 'public')->where('location', $request->input('location'))->get());
    }

    /**
     * Returns a JSON tree of all avaliable IPs and Ports on a given node.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function postNewServerGetIps(Request $request)
    {
        if (! $request->input('node')) {
            return response()->json([
                'error' => 'Missing node in request.',
            ], 500);
        }

        $ips = Models\Allocation::where('node', $request->input('node'))->whereNull('assigned_to')->get();
        $listing = [];

        foreach ($ips as &$ip) {
            if (array_key_exists($ip->ip, $listing)) {
                $listing[$ip->ip] = array_merge($listing[$ip->ip], [$ip->port]);
            } else {
                $listing[$ip->ip] = [$ip->port];
            }
        }

        return response()->json($listing);
    }

    /**
     * Returns a JSON tree of all avaliable options for a given service.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function postNewServerServiceOptions(Request $request)
    {
        if (! $request->input('service')) {
            return response()->json([
                'error' => 'Missing service in request.',
            ], 500);
        }

        $service = Models\Service::select('executable', 'startup')->where('id', $request->input('service'))->first();

        return response()->json(Models\ServiceOptions::select('id', 'name', 'docker_image')->where('parent_service', $request->input('service'))->orderBy('name', 'asc')->get());
    }

    /**
     * Returns a JSON tree of all avaliable variables for a given service option.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function postNewServerOptionDetails(Request $request)
    {
        if (! $request->input('option')) {
            return response()->json([
                'error' => 'Missing option in request.',
            ], 500);
        }

        $option = Models\ServiceOptions::select(
                DB::raw('COALESCE(service_options.executable, services.executable) as executable'),
                DB::raw('COALESCE(service_options.startup, services.startup) as startup')
            )->leftJoin('services', 'services.id', '=', 'service_options.parent_service')
            ->where('service_options.id', $request->input('option'))
            ->first();

        return response()->json([
            'packs' => Models\ServicePack::select('id', 'name', 'version')->where('option', $request->input('option'))->where('selectable', true)->get(),
            'variables' => Models\ServiceVariables::where('option_id', $request->input('option'))->get(),
            'exec' => $option->executable,
            'startup' => $option->startup,
        ]);
    }

    public function postUpdateServerDetails(Request $request, $id)
    {
        try {
            $server = new ServerRepository;
            $server->updateDetails($id, [
                'owner' => $request->input('owner'),
                'name' => $request->input('name'),
                'reset_token' => ($request->input('reset_token', false) === 'on') ? true : false,
            ]);

            Alert::success('Server details were successfully updated.')->flash();
        } catch (DisplayValidationException $ex) {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_details',
            ])->withErrors(json_decode($ex->getMessage()))->withInput();
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled exception occured while attemping to update this server. Please try again.')->flash();
        }

        return redirect()->route('admin.servers.view', [
            'id' => $id,
            'tab' => 'tab_details',
        ])->withInput();
    }

    public function postUpdateContainerDetails(Request $request, $id)
    {
        try {
            $server = new ServerRepository;
            $server->updateContainer($id, [
                'image' => $request->input('docker_image'),
            ]);
            Alert::success('Successfully updated this server\'s docker image.')->flash();
        } catch (DisplayValidationException $ex) {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_details',
            ])->withErrors(json_decode($ex->getMessage()))->withInput();
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled exception occured while attemping to update this server\'s docker image. Please try again.')->flash();
        }

        return redirect()->route('admin.servers.view', [
            'id' => $id,
            'tab' => 'tab_details',
        ]);
    }

    public function postUpdateServerToggleBuild(Request $request, $id)
    {
        $server = Models\Server::findOrFail($id);
        $node = Models\Node::findOrFail($server->node);
        $client = Models\Node::guzzleRequest($server->node);

        try {
            $res = $client->request('POST', '/server/rebuild', [
                'headers' => [
                    'X-Access-Server' => $server->uuid,
                    'X-Access-Token' => $node->daemonSecret,
                ],
            ]);
            Alert::success('A rebuild has been queued successfully. It will run the next time this server is booted.')->flash();
        } catch (\GuzzleHttp\Exception\TransferException $ex) {
            Log::warning($ex);
            Alert::danger('An error occured while attempting to toggle a rebuild.')->flash();
        }

        return redirect()->route('admin.servers.view', [
            'id' => $id,
            'tab' => 'tab_manage',
        ]);
    }

    public function postUpdateServerUpdateBuild(Request $request, $id)
    {
        try {
            $server = new ServerRepository;
            $server->changeBuild($id, [
                'default' => $request->input('default'),
                'add_additional' => $request->input('add_additional'),
                'remove_additional' => $request->input('remove_additional'),
                'memory' => $request->input('memory'),
                'swap' => $request->input('swap'),
                'io' => $request->input('io'),
                'cpu' => $request->input('cpu'),
            ]);
            Alert::success('Server details were successfully updated.')->flash();
        } catch (DisplayValidationException $ex) {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_build',
            ])->withErrors(json_decode($ex->getMessage()))->withInput();
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();

            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_build',
            ]);
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled exception occured while attemping to add this server. Please try again.')->flash();
        }

        return redirect()->route('admin.servers.view', [
            'id' => $id,
            'tab' => 'tab_build',
        ]);
    }

    public function deleteServer(Request $request, $id, $force = null)
    {
        try {
            $server = new ServerRepository;
            $server->deleteServer($id, $force);
            Alert::success('Server has been marked for deletion on the system.')->flash();

            return redirect()->route('admin.servers');
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled exception occured while attemping to delete this server. Please try again.')->flash();
        }

        return redirect()->route('admin.servers.view', [
            'id' => $id,
            'tab' => 'tab_delete',
        ]);
    }

    public function postToggleInstall(Request $request, $id)
    {
        try {
            $server = new ServerRepository;
            $server->toggleInstall($id);
            Alert::success('Server status was successfully toggled.')->flash();
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled exception occured while attemping to toggle this servers status.')->flash();
        } finally {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_manage',
            ]);
        }
    }

    public function postUpdateServerStartup(Request $request, $id)
    {
        try {
            $server = new ServerRepository;
            $server->updateStartup($id, $request->except([
                '_token',
            ]), true);
            Alert::success('Server startup variables were successfully updated.')->flash();
        } catch (\Pterodactyl\Exceptions\DisplayException $e) {
            Alert::danger($e->getMessage())->flash();
        } catch (\Exception $e) {
            Log::error($e);
            Alert::danger('An unhandled exception occured while attemping to update startup variables for this server. Please try again.')->flash();
        } finally {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_startup',
            ])->withInput();
        }
    }

    public function postDatabase(Request $request, $id)
    {
        try {
            $repo = new DatabaseRepository;
            $repo->create($id, $request->except([
                '_token',
            ]));
            Alert::success('Added new database to this server.')->flash();
        } catch (DisplayValidationException $ex) {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_database',
            ])->withInput()->withErrors(json_decode($ex->getMessage()))->withInput();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An exception occured while attempting to add a new database for this server.')->flash();
        }

        return redirect()->route('admin.servers.view', [
            'id' => $id,
            'tab' => 'tab_database',
        ])->withInput();
    }

    public function postSuspendServer(Request $request, $id)
    {
        try {
            $repo = new ServerRepository;
            $repo->suspend($id);
            Alert::success('Server has been suspended on the system. All running processes have been stopped and will not be startable until it is un-suspended.');
        } catch (DisplayException $e) {
            Alert::danger($e->getMessage())->flash();
        } catch (\Exception $e) {
            Log::error($e);
            Alert::danger('An unhandled exception occured while attemping to suspend this server. Please try again.')->flash();
        } finally {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_manage',
            ]);
        }
    }

    public function postUnsuspendServer(Request $request, $id)
    {
        try {
            $repo = new ServerRepository;
            $repo->unsuspend($id);
            Alert::success('Server has been unsuspended on the system. Access has been re-enabled.');
        } catch (DisplayException $e) {
            Alert::danger($e->getMessage())->flash();
        } catch (\Exception $e) {
            Log::error($e);
            Alert::danger('An unhandled exception occured while attemping to unsuspend this server. Please try again.')->flash();
        } finally {
            return redirect()->route('admin.servers.view', [
                'id' => $id,
                'tab' => 'tab_manage',
            ]);
        }
    }

    public function postQueuedDeletionHandler(Request $request, $id)
    {
        try {
            $repo = new ServerRepository;
            if (! is_null($request->input('cancel'))) {
                $repo->cancelDeletion($id);
                Alert::success('Server deletion has been cancelled. This server will remain suspended until you unsuspend it.')->flash();

                return redirect()->route('admin.servers.view', $id);
            } elseif (! is_null($request->input('delete'))) {
                $repo->deleteNow($id);
                Alert::success('Server was successfully deleted from the system.')->flash();

                return redirect()->route('admin.servers');
            } elseif (! is_null($request->input('force_delete'))) {
                $repo->deleteNow($id, true);
                Alert::success('Server was successfully force deleted from the system.')->flash();

                return redirect()->route('admin.servers');
            }
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();

            return redirect()->route('admin.servers.view', $id);
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unhandled error occured while attempting to perform this action.')->flash();

            return redirect()->route('admin.servers.view', $id);
        }
    }
}
