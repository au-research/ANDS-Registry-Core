<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400,600,700,900,300italic,400italic,600italic' rel='stylesheet' type='text/css' />
<link rel="stylesheet" type="text/css" href="{{ base_url() }}apps/assets/vocab_widget/css/vocab_widget.css" media="screen" />
@if(is_dev())
<link rel="stylesheet" href="{{asset_url('ands-green/css/bootstrap.min.css','templates')}}" />
<link rel="stylesheet" href="{{asset_url('ands-green/css/theme.css','templates')}}" />
<link rel="stylesheet" href="{{asset_url('ands-green/css/swatch-gray.css','templates')}}" media="screen" />
<link rel="stylesheet" href="{{asset_url('ands-green/css/swatch-white.css','templates')}}" media="screen" />
<link rel="stylesheet" href="{{asset_url('ands-green/css/swatch-black.min.css','templates')}}" media="screen" />
<link rel="stylesheet" href="{{asset_url('ands-green/css/fonts.min.css','templates')}}" media="screen" />
<link rel="stylesheet" href="{{asset_url('lib/qtip2/jquery.qtip.css', 'core')}}" media="screen" />
<link rel="stylesheet" href="{{asset_url('lib/angular-loading-bar/build/loading-bar.min.css', 'core')}}" />
<link rel="stylesheet" href="{{asset_url('js/lib/ui-select/dist/select.css')}}" />
<link rel="stylesheet" href="{{asset_url('css/aui-just-lozenges.css')}}" />
<!-- LESS file for development only -->
<link rel="stylesheet/less" type="text/css" href="{{asset_url('less/ands-vocab.less')}}" media="screen" />
@else
<link rel="stylesheet" type="text/css" href="{{ asset_url('css/lib.css').'?'.getReleaseVersion() }}" media="screen" />
<link rel="stylesheet" type="text/css" href="{{ asset_url('css/vocab.less.compiled.css').'?'.getReleaseVersion() }}" media="screen" />

@endif