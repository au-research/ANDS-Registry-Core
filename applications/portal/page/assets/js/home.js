$(document).ready(function() {

   $('#slider').flexslider({
       animation: "slide",
       animationLoop:true,
       slideshowSpeed: 25002500,
       pauseOnHover:true,
       directionNav:true,
       controlNav: false,
       prevText: '',
       nextText: '',
       itemWidth: 240,
       itemMargin: 10,
       // smoothHeight: true,
       move:1,
       minItems: 4,
     });             
});