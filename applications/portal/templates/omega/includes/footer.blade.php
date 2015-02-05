@include('includes/advanced_search')
<footer id="footer" role="contentinfo">
    <section class="section swatch-black">
        <div class="container">
            <div class="row element-normal-top element-normal-bottom">
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Quick Links</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Home</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/about')}}" title="">About</a> </li>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">My RDA</a> </li>
                            <li class="cat-item myCustomTrigger"> <a href="{{portal_url('page/contact')}}" title="">Contact us</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/disclaimer')}}" title="">Disclaimer</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/privacy')}}" title="">Privacy Policy</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/help')}}" title="">Help</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Explore</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Themed Collections</a> </li>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Open Data</a> </li>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Tools and Services</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('search/#!/class=activity')}}" title="">Projects and Grants</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-4" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">External Resources</h3>
                        <ul>
                            <li class="cat-item"> <a href="http://www.ands.org.au/" title="">ANDS Website</a> </li>
                            <li class="cat-item"> <a href="http://developers.ands.org.au" title="">Developers</a> </li>
                            <li class="cat-item"> <a href="{{base_url('')}}" title="">ANDS Online Services</a> </li>
                            @if(isset($ro) && $ro->core['id'])
                                <li class="cat-item"> <a href="{{base_url('registry/registry_object/view/')}}/<?=$this->ro->id?>" title="">Registry View</a> </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>
@include('includes/scripts')