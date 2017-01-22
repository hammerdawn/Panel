<div class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.services.packs.uploadForm') }}" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Install Pack from Template</h4>
                </div>
                <div class="modal-body">
                    <div class="well" style="margin-bottom:0">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="control-label">Associated Service Option:</label>
                                <select name="option" class="form-control">
                                    @foreach($services as $service => $options)
                                        <option disabled>{{ $service }}</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option['id'] }}" @if((int) $for === (int) $option['id'])selected="selected"@endif>&nbsp;&nbsp; -- {{ $option['name'] }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top:15px;">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label class="control-label">Package Archive:</label>
                                        <input name="file_upload" type="file" accept=".zip,.json, application/json, application/zip" />
                                        <p class="text-muted"><small>This file should be either the <code>.json</code> template file, or a <code>.zip</code> pack archive containing <code>archive.(zip|tar.gz)</code> and <code>import.json</code> within.<br /><br />This server is currently configured with the following limits: <code>upload_max_filesize={{ ini_get('upload_max_filesize') }}</code> and <code>post_max_size={{ ini_get('post_max_size') }}</code>. If your file is larger than either of those values this request will fail.</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {!! csrf_field() !!}
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    <input type="submit" class="btn btn-primary btn-sm" value="Install" />
                </div>
            </form>
        </div>
    </div>
</div>
