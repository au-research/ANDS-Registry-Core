<footer id="footer" role="contentinfo">
    <section class="section swatch-black">
        <div class="container">
            <div class="row element-normal-top element-normal-bottom">
                <div class="col-md-8">
                    <p>
                        Research Data Australia is the data discovery service of the Australian National Data Service (ANDS). ANDS is supported by the Australian Government through the National Collaborative Research Infrastructure Strategy Program. Read more <a href="http://www.ands.org.au/andsbrochurejan2015.pdf">about ANDS</a>...
                    </p>
                </div>
                <div class="col-md-2">
                   <a href="https://education.gov.au/national-collaborative-research-infrastructure-strategy-ncris" target="_blank" class="gov_logo"><img style="height:75px;" src="<?php echo asset_url('images/NCRIS_PROVIDER_rev.png','core');?>" alt="National Collaborative Research Infrastructure Strategy (NCRIS)" /></a>
                </div>
                <div class="col-md-2">
                    <a href="http://www.ands.org.au/" class="footer_logo"><img src="{{asset_url('images/footer_logo_rev.png', 'core')}}" alt="" style="height:75px;"/></a>    
                </div>
            </div>
            <div class="row element-normal-top element-normal-bottom">
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Quick Links</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Home</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/about')}}" title="">About</a> </li>
                            <li class="cat-item"> <a href="{{portal_url()}}profile" title="">My RDA</a> </li>
                            <li class="cat-item myCustomTrigger"> <a href="" title="">Contact us</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/disclaimer')}}" title="">Disclaimer</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/privacy')}}" title="">Privacy Policy</a> </li>
                            <!-- <li class="cat-item"> <a href="{{portal_url('page/help')}}" title="">Help</a> </li> -->
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Explore</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url('themes')}}" title="">Themed Collections</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('theme/open-data')}}#!/access_rights=open" title="">Open Data</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('theme/services')}}#!/class=service" title="">Tools and Services</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('grants')}}" title="">Projects and Grants</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php
                        $url = ((isset($ro) && isset($ro->core['slug']) && isset($ro->core['id'])) ? base_url().$ro->core['slug'].'/'.$ro->core['id'] : current_url() );
                        $title = ((isset($ro) && isset($ro->core['slug']) && isset($ro->core['id'])) ? $ro->core['title']. ' - Research Data Australia' : 'Research Data Australia' );
                    ?>
                    <div id="categories-5" class="sidebar-widget widget_categories">
                        <h3 class="sidebar-header">Share</h3>
                        <ul>
                            <li class="cat-item"><a class="noexicon" href="http://www.facebook.com/sharer.php?u={{$url}}" target="_blank"><i class="fa fa-facebook"></i> Facebook</a></li>
                            <li class="cat-item"><a class="noexicon" href="https://twitter.com/share?url={{$url}}&text={{$title}}&hashtags=andsdata" target="_blank"><i class="fa fa-twitter"></i> Twitter</a></li>
                            <li class="cat-item"><a class="noexicon" href="https://plus.google.com/share?url={{$url}}" target="_blank"><i class="fa fa-google"></i> Google</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-4" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">External Resources</h3>
                        <ul>
                            <li class="cat-item"> <a href="http://www.ands.org.au/" title="" target="_blank">ANDS Website</a> </li>
                            <li class="cat-item"> <a href="http://developers.ands.org.au" title="" target="_blank">Developers</a> </li>
                            <li class="cat-item"> <a href="{{base_url('registry/')}}" title="">ANDS Online Services</a> </li>
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