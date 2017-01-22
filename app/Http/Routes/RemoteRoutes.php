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

namespace Pterodactyl\Http\Routes;

use Illuminate\Routing\Router;

class RemoteRoutes
{
    public function map(Router $router)
    {
        $router->group(['prefix' => 'remote'], function () use ($router) {
            // Handles Remote Download Authentication Requests
            $router->post('download', [
                'as' => 'remote.download',
                'uses' => 'Remote\RemoteController@postDownload',
            ]);

            $router->post('install', [
                'as' => 'remote.install',
                'uses' => 'Remote\RemoteController@postInstall',
            ]);

            $router->post('event', [
                'as' => 'remote.event',
                'uses' => 'Remote\RemoteController@event',
            ]);

            $router->get('configuration/{token}', [
                'as' => 'remote.configuration',
                'uses' => 'Remote\RemoteController@getConfiguration',
            ]);
        });
    }
}
