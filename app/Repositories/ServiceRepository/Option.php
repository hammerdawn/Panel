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

namespace Pterodactyl\Repositories\ServiceRepository;

use DB;
use Validator;
use Pterodactyl\Models;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Exceptions\DisplayValidationException;

class Option
{
    public function __construct()
    {
        //
    }

    public function create($service, array $data)
    {
        $service = Models\Service::findOrFail($service);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:1',
            'tag' => 'required|string|max:255',
            'executable' => 'sometimes|string|max:255',
            'docker_image' => 'required|string|max:255',
            'startup' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new DisplayValidationException($validator->errors());
        }

        if (isset($data['executable']) && empty($data['executable'])) {
            $data['executable'] = null;
        }

        if (isset($data['startup']) && empty($data['startup'])) {
            $data['startup'] = null;
        }

        $option = new Models\ServiceOptions;
        $option->parent_service = $service->id;
        $option->fill($data);
        $option->save();

        return $option->id;
    }

    public function delete($id)
    {
        $option = Models\ServiceOptions::findOrFail($id);
        $servers = Models\Server::where('option', $option->id)->get();

        if (count($servers) !== 0) {
            throw new DisplayException('You cannot delete an option that has servers attached to it currently.');
        }

        DB::beginTransaction();

        try {
            Models\ServiceVariables::where('option_id', $option->id)->delete();
            $option->delete();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function update($id, array $data)
    {
        $option = Models\ServiceOptions::findOrFail($id);

        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:1',
            'tag' => 'sometimes|required|string|max:255',
            'executable' => 'sometimes|string|max:255',
            'docker_image' => 'sometimes|required|string|max:255',
            'startup' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new DisplayValidationException($validator->errors());
        }

        if (isset($data['executable']) && empty($data['executable'])) {
            $data['executable'] = null;
        }

        if (isset($data['startup']) && empty($data['startup'])) {
            $data['startup'] = null;
        }

        $option->fill($data);

        return $option->save();
    }
}
