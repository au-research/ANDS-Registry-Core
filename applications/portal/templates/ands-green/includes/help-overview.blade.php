<?php
$this->load->library('user_agent');
$useIFrame = true;
if ($this->agent->is_browser('Chrome'))
{
    $useIFrame = false;
}

?>
<ul style="list-style-type: none;">
    <li><a href="#intro_video">Introduction to Research Data Australia</a></li>
    <li><a href="#search_video_ov">How to search in Research Data Australia</a></li>
    <li><a href="#my_rda_video_ov">How to use MyRDA in Research Data Australia</a></li>

</ul>
<p>Welcome to Research Data Australia help. Watch the videos below to get yourself acquainted with site, or use the tabs displayed above to navigate to specific help sections.</p>
<p>Note that the help can be accessed at any time by using the orange ‘Help’ button displayed in the bottom right hand corner of every page.</p>
<!-- <iframe width="560" height="315" src="https://www.youtube.com/embed/spWa9ZQM21A?list=PLG25fMbdLRa5jt_fqievJtLbIMSJDb1-7" frameborder="0" allowfullscreen></iframe> -->
<br/>
<h3 id="intro_video">Introduction to Research Data Australia</h3>
<br/>
@if($useIFrame)
<iframe width="560" height="315" src="https://www.youtube.com/embed/W9GlD9CJjhk" frameborder="0" allowfullscreen></iframe>
@else
<object width="560" height="315">
        <param name="movie" value="https://www.youtube.com/v/W9GlD9CJjhk?version=3&hl=en_US"></param>
        <param name="allowFullScreen" value="true"></param>
        <param name="allowscriptaccess" value="always"></param>
        <embed
            src="https://www.youtube.com/v/W9GlD9CJjhk?version=3&hl=en_US"
            type="application/x-shockwave-flash" width="560" height="315"
            allowscriptaccess="always"
            allowfullscreen="true">
        </embed>
    </object>
@endif
<br/><br/>
<h3 id="search_video_ov">How to search in Research Data Australia</h3>
<br/>
@if($useIFrame)
<iframe width="560" height="315" src="https://www.youtube.com/embed/MZGb2tqF2Pw" frameborder="0" allowfullscreen></iframe>
@else
<object width="560" height="315">
    <param name="movie" value="https://www.youtube.com/v/MZGb2tqF2Pw?version=3&hl=en_US"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed
        src="https://www.youtube.com/v/MZGb2tqF2Pw?version=3&hl=en_US"
        type="application/x-shockwave-flash" width="560" height="315"
        allowscriptaccess="always"
        allowfullscreen="true">
    </embed>
</object>
@endif
<br/><br/>
<h3 id="my_rda_video_ov">How to use MyRDA in Research Data Australia</h3>
<br/>
@if($useIFrame)
<iframe width="560" height="315" src="https://www.youtube.com/embed/C2HImxMDY3c" frameborder="0" allowfullscreen></iframe>
@else
<object width="560" height="315">
    <param name="movie" value="https://www.youtube.com/v/C2HImxMDY3c?version=3&hl=en_US"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed
        src="https://www.youtube.com/v/C2HImxMDY3c?version=3&hl=en_US"
        type="application/x-shockwave-flash" width="560" height="315"
        allowscriptaccess="always"
        allowfullscreen="true">
    </embed>
</object>
@endif