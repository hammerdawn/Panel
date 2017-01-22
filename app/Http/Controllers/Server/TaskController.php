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

namespace Pterodactyl\Http\Controllers\Server;

use Log;
use Alert;
use Javascript;
use Pterodactyl\Models;
use Illuminate\Http\Request;
use Pterodactyl\Repositories;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Exceptions\DisplayValidationException;

class TaskController extends Controller
{
    public function __constructor()
    {
        //
    }

    public function getIndex(Request $request, $uuid)
    {
        $server = Models\Server::getByUUID($uuid);
        $this->authorize('list-tasks', $server);
        $node = Models\Node::find($server->node);

        Javascript::put([
            'server' => collect($server->makeVisible('daemonSecret'))->only(['uuid', 'uuidShort', 'daemonSecret', 'username']),
            'node' => collect($node)->only('fqdn', 'scheme', 'daemonListen'),
        ]);

        return view('server.tasks.index', [
            'server' => $server,
            'node' => $node,
            'tasks' => Models\Task::where('server', $server->id)->get(),
            'actions' => [
                'command' => trans('server.tasks.actions.command'),
                'power' => trans('server.tasks.actions.power'),
            ],
        ]);
    }

    public function getNew(Request $request, $uuid)
    {
        $server = Models\Server::getByUUID($uuid);
        $this->authorize('create-task', $server);
        $node = Models\Node::find($server->node);

        Javascript::put([
            'server' => collect($server->makeVisible('daemonSecret'))->only(['uuid', 'uuidShort', 'daemonSecret', 'username']),
            'node' => collect($node)->only('fqdn', 'scheme', 'daemonListen'),
        ]);

        return view('server.tasks.new', [
            'server' => $server,
            'node' => $node,
        ]);
    }

    public function postNew(Request $request, $uuid)
    {
        $server = Models\Server::getByUUID($uuid);
        $this->authorize('create-task', $server);

        try {
            $repo = new Repositories\TaskRepository;
            $repo->create($server->id, $request->except([
                '_token',
            ]));

            return redirect()->route('server.tasks', $uuid);
        } catch (DisplayValidationException $ex) {
            return redirect()->route('server.tasks.new', $uuid)->withErrors(json_decode($ex->getMessage()))->withInput();
        } catch (DisplayException $ex) {
            Alert::danger($ex->getMessage())->flash();
        } catch (\Exception $ex) {
            Log::error($ex);
            Alert::danger('An unknown error occured while attempting to create this task.')->flash();
        }

        return redirect()->route('server.tasks.new', $uuid);
    }

    public function deleteTask(Request $request, $uuid, $id)
    {
        $server = Models\Server::getByUUID($uuid);
        $this->authorize('delete-task', $server);

        $task = Models\Task::findOrFail($id);

        if (! $task || $server->id !== $task->server) {
            return response()->json([
                'error' => 'No task by that ID was found associated with this server.',
            ], 404);
        }

        try {
            $repo = new Repositories\TaskRepository;
            $repo->delete($id);

            return response()->json([], 204);
        } catch (\Exception $ex) {
            Log::error($ex);

            return response()->json([
                'error' => 'A server error occured while attempting to delete this task.',
            ], 503);
        }
    }

    public function toggleTask(Request $request, $uuid, $id)
    {
        $server = Models\Server::getByUUID($uuid);
        $this->authorize('toggle-task', $server);

        $task = Models\Task::findOrFail($id);

        if (! $task || $server->id !== $task->server) {
            return response()->json([
                'error' => 'No task by that ID was found associated with this server.',
            ], 404);
        }

        try {
            $repo = new Repositories\TaskRepository;
            $resp = $repo->toggle($id);

            return response()->json([
                'status' => $resp,
            ]);
        } catch (\Exception $ex) {
            Log::error($ex);

            return response()->json([
                'error' => 'A server error occured while attempting to toggle this task.',
            ], 503);
        }
    }
}
