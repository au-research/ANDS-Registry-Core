@if(is_dev())
<link rel="stylesheet" href="{{asset_url('ands-green/js/neo4jd3/css/font-awesome.min.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/js/neo4jd3/css/neo4jd3.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/bootstrap.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/theme.min.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/swatch-gray.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/swatch-black.min.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/swatch-ands-green.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/fonts.min.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('lib/dynatree/dist/skin/ui.dynatree.css', 'core')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('lib/qtip2/jquery.qtip.css', 'core')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('lib/angular-loading-bar/build/loading-bar.min.css', 'core')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/css/ands.css','templates')}}" media="screen">
<!-- LESS file for development only -->
<link rel="stylesheet/less" type="text/css" href="{{asset_url('ands-green/less/ands-portal.less','templates')}}" media="screen">
<link rel="stylesheet/less" type="text/css" href="{{asset_url('ands-green/less/print.less','templates')}}" media="print">
<link rel="stylesheet" href="{{asset_url('ands-green/js/neo4jd3/css/font-awesome.min.css','templates')}}" media="screen">
<link rel="stylesheet" href="{{asset_url('ands-green/js/neo4jd3/css/neo4jd3.css','templates')}}" media="screen">
@else
<link rel="stylesheet" type="text/css" href="{{asset_url('css/portal.combine.css', 'core')}}" media="screen">
<link rel="stylesheet" type="text/css" href="{{asset_url('css/print.css', 'core')}}" media="print">
@endif