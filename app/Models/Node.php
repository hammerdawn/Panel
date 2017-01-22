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

namespace Pterodactyl\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nodes';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['daemonSecret'];

     /**
      * Cast values to correct type.
      *
      * @var array
      */
     protected $casts = [
         'public' => 'integer',
         'location' => 'integer',
         'memory' => 'integer',
         'disk' => 'integer',
         'daemonListen' => 'integer',
         'daemonSFTP' => 'integer',
     ];

    /**
     * Fields that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected static $guzzle = [];

    /**
     * @var array
     */
    protected static $nodes = [];

    /**
     * Returns an instance of the database object for the requested node ID.
     *
     * @param  int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getByID($id)
    {

        // The Node is already cached.
        if (array_key_exists($id, self::$nodes)) {
            return self::$nodes[$id];
        }

        self::$nodes[$id] = self::where('id', $id)->first();

        return self::$nodes[$id];
    }

    /**
     * Returns a Guzzle Client for the node in question.
     *
     * @param  int $node
     * @return \GuzzleHttp\Client
     */
    public static function guzzleRequest($node)
    {

        // The Guzzle Client is cached already.
        if (array_key_exists($node, self::$guzzle)) {
            return self::$guzzle[$node];
        }

        $nodeData = self::getByID($node);

        self::$guzzle[$node] = new Client([
            'base_uri' => sprintf('%s://%s:%s/', $nodeData->scheme, $nodeData->fqdn, $nodeData->daemonListen),
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
        ]);

        return self::$guzzle[$node];
    }

    /**
     * Returns the configuration in JSON format.
     *
     * @param bool $pretty Wether to pretty print the JSON or not
     * @return string The configration in JSON format
     */
    public function getConfigurationAsJson($pretty = false)
    {
        $config = [
            'web' => [
                'host' => '0.0.0.0',
                'listen' => $this->daemonListen,
                'ssl' => [
                    'enabled' => $this->scheme === 'https',
                    'certificate' => '/etc/letsencrypt/live/localhost/fullchain.pem',
                    'key' => '/etc/letsencrypt/live/localhost/privkey.pem',
                ],
            ],
            'docker' => [
                'socket' => '/var/run/docker.sock',
                'autoupdate_images' => true,
            ],
            'sftp' => [
                'path' => $this->daemonBase,
                'port' => $this->daemonSFTP,
                'container' => 'ptdl-sftp',
            ],
            'query' => [
                'kill_on_fail' => true,
                'fail_limit' => 5,
            ],
            'logger' => [
                'path' => 'logs/',
                'src' => false,
                'level' => 'info',
                'period' => '1d',
                'count' => 3,
            ],
            'remote' => [
                'base' => config('app.url'),
                'download' => route('remote.download'),
                'installed' => route('remote.install'),
            ],
            'uploads' => [
                'size_limit' => $this->upload_size,
            ],
            'keys' => [$this->daemonSecret],
        ];

        $json_options = JSON_UNESCAPED_SLASHES;
        if ($pretty) {
            $json_options |= JSON_PRETTY_PRINT;
        }

        return json_encode($config, $json_options);
    }
}
