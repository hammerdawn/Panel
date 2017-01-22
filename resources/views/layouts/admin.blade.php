{{-- Copyright (c) 2015 - 2016 Dane Everitt <dane@daneeveritt.com> --}}

{{-- Permission is hereby granted, free of charge, to any person obtaining a copy --}}
{{-- of this software and associated documentation files (the "Software"), to deal --}}
{{-- in the Software without restriction, including without limitation the rights --}}
{{-- to use, copy, modify, merge, publish, distribute, sublicense, and/or sell --}}
{{-- copies of the Software, and to permit persons to whom the Software is --}}
{{-- furnished to do so, subject to the following conditions: --}}

{{-- The above copyright notice and this permission notice shall be included in all --}}
{{-- copies or substantial portions of the Software. --}}

{{-- THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR --}}
{{-- IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, --}}
{{-- FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE --}}
{{-- AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER --}}
{{-- LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, --}}
{{-- OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE --}}
{{-- SOFTWARE. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    @section('scripts')
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex">
        {!! Theme::css('css/vendor/bootstrap/bootstrap.css') !!}
        {!! Theme::css('css/pterodactyl.css') !!}
        {!! Theme::css('css/animate.css') !!}
        {!! Theme::css('css/vendor/fontawesome/font-awesome.min.css') !!}
        {!! Theme::css('css/vendor/sweetalert/sweetalert.min.css') !!}
        {!! Theme::css('css/vendor/fuelux/fuelux.min.css') !!}
        {!! Theme::js('js/vendor/jquery/jquery.min.js') !!}
        {!! Theme::js('js/vendor/bootstrap/bootstrap.min.js') !!}
        {!! Theme::js('js/vendor/sweetalert/sweetalert.min.js') !!}
        {!! Theme::js('js/vendor/fuelux/fuelux.min.js') !!}
        {!! Theme::js('js/admin.min.js') !!}
        {!! Theme::js('js/bootstrap-notify.min.js') !!}
        <script>
            $(document).ready(function () {
                $.notifyDefaults({
                    placement: {
                        from: 'bottom',
                        align: 'right'
                    },
                    newest_on_top: true,
                    delay: 2000,
                    animate: {
                        enter: 'animated fadeInUp',
                        exit: 'animated fadeOutDown'
                    }
                });
            });
        </script>
    @show
    <title>{{ Settings::get('company') }} - @yield('title')</title>
</head>
<body>
    <div class="container">
        <div class="navbar navbar-default">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/">{{ Settings::get('company', 'Pterodactyl Panel') }}</a>
            </div>
            <div class="navbar-collapse collapse navbar-responsive-collapse">
                @section('navbar-links')
                    <ul class="nav navbar-nav hidden-md hidden-lg">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Management <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/admin">Admin Index</a></li>
                                <li><a href="/admin/settings">General Settings</a></li>
                                <li><a href="/admin/databases">Database Management</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Users <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/admin/users">Find Account</a></li>
                                <li><a href="/admin/users/new">New Account</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Servers <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/admin/servers">Find Server</a></li>
                                <li><a href="/admin/servers/new">New Server</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Nodes <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/admin/nodes">List Nodes</a></li>
                                <li><a href="/admin/locations">Manage Locations</a></li>
                                <li><a href="/admin/nodes/new">New Node</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Services <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/admin/services/packs">Service Packs</a></li>
                                <li><a href="/admin/services">List Services</a></li>
                                <li><a href="/admin/services/new">Add Service</a></li>
                            </ul>
                        </li>
                    </ul>
                @show
                @section('right-nav')
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ trans('strings.language') }}<b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/language/de">Deutsch</a></li>
                                <li><a href="/language/en">English</a></li>
                                <!-- <li><a href="/language/es">Espa&ntilde;ol</a></li>
                                <li><a href="/language/fr">Fran&ccedil;ais</a></li>
                                <li><a href="/language/it">Italiano</a></li>
                                <li><a href="/language/pl">Polski</a></li> -->
                                <li><a href="/language/pt">Portugu&ecirc;s</a></li>
                                <!-- <li><a href="/language/ru">&#1088;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;</a></li>
                                <li><a href="/language/se">Svenska</a></li>
                                <li><a href="/language/zh">&#20013;&#22269;&#30340;的</a></li> -->
                            </ul>
                        </li>
                        <li class="hidden-xs"><a href="/"><i class="fa fa-server"></i></a></li>
                        <li class="visible-xs"><a href="/"><i class="fa fa-server"></i> Server View</a></li>
                        <li class="hidden-xs"><a href="/auth/logout"><i class="fa fa-power-off"></i></a></li>
                        <li class="visible-xs"><a href="/auth/logout"><i class="fa fa-power-off"></i> Logout</a></li>
                    </ul>
                @show
            </div>
        </div>
        <!-- Add Back Mobile Support -->
        <div class="row">
            <div class="col-md-3 hidden-xs hidden-sm" id="sidebar_links">
                @section('sidebar')
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-heading"><strong>Management</strong></a>
                        <a href="/admin" id="sidenav_admin-index" class="list-group-item">Admin Index</a>
                        <a href="/admin/settings" class="list-group-item">General Settings</a>
                        <a href="/admin/databases" class="list-group-item">Database Management</a>
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-heading"><strong>Account Management</strong></a>
                        <a href="/admin/users" class="list-group-item">Find Account</a>
                        <a href="/admin/users/new" class="list-group-item">New Account</a>
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-heading"><strong>Server Management</strong></a>
                        <a href="/admin/servers" class="list-group-item">Find Server</a>
                        <a href="/admin/servers/new" class="list-group-item">New Server</a>
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-heading"><strong>Node Management</strong></a>
                        <a href="/admin/nodes" class="list-group-item">List Nodes</a>
                        <a href="/admin/locations" class="list-group-item">Manage Locations</a>
                        <a href="/admin/nodes/new" class="list-group-item">Add Node</a>
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-heading"><strong>Service Management</strong></a>
                        <a href="/admin/services/packs" class="list-group-item">Service Packs</a>
                        <a href="/admin/services" class="list-group-item">List Services</a>
                        <a href="/admin/services/new" class="list-group-item">Add Service</a>
                    </div>
                @show
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-12" id="tpl_messages">
                        @section('resp-errors')
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <strong>{{ trans('strings.whoops') }}!</strong> {{ trans('auth.errorencountered') }}<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @show
                        @section('resp-alerts')
                            @foreach (Alert::getMessages() as $type => $messages)
                                @foreach ($messages as $message)
                                    <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        {!! $message !!}
                                    </div>
                                @endforeach
                            @endforeach
                        @show
                    </div>
                </div>
                <div class="row">
                    @yield('content')
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="row" style="margin-bottom:15px;">
                <div class="col-md-12">
                    Copyright &copy; 2015 - {{ date('Y') }} <a href="https://github.com/Pterodactyl/Panel" target="_blank">Pterodactyl Software &amp; Design</a>.<br />
                    Pterodactyl is licensed under a <a href="https://opensource.org/licenses/MIT" target="_blank">MIT</a> license. <!-- Please do not remove this license notice. We can't stop you though... :) -->
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function () {
        // Remeber Active Tab and Navigate to it on Reload
        for(var queryParameters={},queryString=location.search.substring(1),re=/([^&=]+)=([^&]*)/g,m;m=re.exec(queryString);)queryParameters[decodeURIComponent(m[1])]=decodeURIComponent(m[2]);$("a[data-toggle='tab']").click(function(){queryParameters.tab=$(this).attr("href").substring(1),window.history.pushState(null,null,location.pathname+"?"+$.param(queryParameters))});
        if($.urlParam('tab') != null){$('.nav.nav-tabs a[href="#' + $.urlParam('tab') + '"]').tab('show');}

        @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
                <?php preg_match('/^The\s(.*?)\sfield/', $error, $matches) ?>
                @if (isset($matches[1]))
                    $('[name="{{ str_replace(' ', '_', $matches[1]) }}"]').parent().parent().addClass('has-error');
                @endif
            @endforeach
        @endif
    });
    </script>
</body>
</html>
