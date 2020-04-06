<footer id="footer" role="contentinfo">
    <section class="section swatch-black">
        <div class="container">

            <div class="row element-normal-top element-normal-bottom">

                    <div class="col-md-2 ardc-logo-footer">
                        <a href="https://www.ardc.edu.au/" class="footer_logo">
                          <!--   <img src="{{ asset_url(\ANDS\Util\config::get('app.environment_logo'), 'base')}}" alt=""/></a> -->
                        <img src="https://ardc.edu.au/wp-content/themes/ardc/img/ardc_logo.svg" alt="" /></a>
                    </div>

                <div class="col-md-2">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h5 class="sidebar-header">Quick Links</h5>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Home</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/about')}}" title="">About</a> </li>
                            <li class="cat-item"> <a href="{{portal_url()}}profile" title="">My RDA</a> </li>
                            <li class="cat-item myCustomTrigger"> <a href="" title="">Contact us</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/disclaimer')}}" title="">Disclaimer</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/privacy')}}" title="">Privacy Policy</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h5 class="sidebar-header">Explore</h5>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url('themes')}}" title="">Themed Collections</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('theme/open-data')}}#!/access_rights=open" title="">Open Data</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('theme/services')}}#!/class=service" title="">Services and Tools</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('grants')}}" title="">Projects and Grants</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <div id="categories-4" class="sidebar-widget  widget_categories">
                        <h5 class="sidebar-header">External Resources</h5>
                        <ul>
                            <li class="cat-item"> <a href="https://ardc.edu.au" title="" target="_blank">ARDC Website</a> </li>
                            <li class="cat-item"> <a href="{{base_url('registry/')}}" title="">ARDC Online Services</a> </li>
                            @if(isset($ro) && $ro->core['id'])
                                <li class="cat-item"> <a href="{{base_url('registry/registry_object/view/')}}/<?=$this->ro->id?>" title="">Registry View</a> </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <?php

                        $params = [
                            'url' => ((isset($ro) && isset($ro->core['slug']) && isset($ro->core['id'])) ? base_url().$ro->core['slug'].'/'.$ro->core['id'] : current_url() ),
                            'title' => ((isset($ro) && isset($ro->core['slug']) && isset($ro->core['id'])) ? $ro->core['title']. ' - Research Data Australia' : 'Research Data Australia' )
                        ];

                        if (isset($ro) && isset($ro->core['id'])) {
                            $params['id'] = $ro->core['id'];
                        }

                        $params = http_build_query($params);
                    ?>
                    <div id="categories-5" class="sidebar-widget widget_categories">
                        <h5 class="sidebar-header">Share</h5>
                        <ul>
                            <li class="cat-item"><a class="noexicon social-sharing" href="{{ portal_url('page/share/facebook?'.$params) }}" target="_blank"><i class="fa fa-facebook"></i> Facebook</a></li>
                            <li class="cat-item"><a class="noexicon social-sharing" href="{{ portal_url('page/share/twitter/?'.$params) }}" target="_blank"><i class="fa fa-twitter"></i> Twitter</a></li>
                        </ul>
                    </div>
                </div>


                <div class="col-md-2">
                    <p class="footer-text">
                        The Australian Research Data Commons (ARDC) is enabled by NCRIS.
                    </p>
                    <a href="https://education.gov.au/national-collaborative-research-infrastructure-strategy-ncris" target="_blank" class="gov_logo">
                    <img src="<?php echo asset_url('images/NCRIS_PROVIDER_rev.png','core');?>" alt="National Collaborative Research Infrastructure Strategy (NCRIS)" height="100px"/>
                    </a>
                </div>
            </div>
        </div>
    </section>
</footer>