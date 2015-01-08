
    @include('includes/advanced_search')

 <footer id="footer" role="contentinfo">
    <section class="section swatch-black">
        <div class="container">
            <div class="row element-normal-top element-normal-bottom">
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Links</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Home</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/about')}}" title="">About</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/contact')}}" title="">Contact us</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/disclaimer')}}" title="">Disclaimer</a> </li>
                            <li class="cat-item"> <a href="http://developers.ands.org.au" title="">Developers</a> </li>
                            <li class="cat-item"> <a href="{{registry_url()}}" title="">ANDS Online Services</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/privacy')}}" title="">Privacy Policy</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-4" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Registry Contents</h3>
                        <ul>
                            <li class="cat-item"> <a href="#" title="">Collections</a> </li>
                            <li class="cat-item"> <a href="#" title="">Parties</a> </li>
                            <li class="cat-item"> <a href="#" title="">Activities</a> </li>
                            <li class="cat-item"> <a href="#" title="">Services</a> </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>
@include('includes/scripts')