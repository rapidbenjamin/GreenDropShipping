$( document ).ready(function() {
  //console.log("ready");
    /*$(".mainmenu li").click(function(e) {
        e.preventDefault();        
        if(!$(this).hasClass("active")){
            $(".mainmenu li").removeClass("active");
        }
        
        $(this).toggleClass("active");
    });*/
    
    
    $(window).on('resize', function(){
          var win = $(this);
          if (win.width() <= 800) {
              
                var slideout = new Slideout({
                    'panel': document.getElementById('panel'),
                    'menu': document.getElementById('menu'),
                    'padding': 256,
                    'tolerance': 70
                });

                // Toggle button
                document.querySelector('.toggle-button').addEventListener('click', function() {
                    slideout.toggle();
                    $(".toggle-button").toggleClass("open");
                });
              
          }
    });
    
    if($(window).width() <= 800) {
        var slideout = new Slideout({
            'panel': document.getElementById('panel'),
            'menu': document.getElementById('menu'),
            'padding': 256,
            'tolerance': 70
        });

        // Toggle button
        document.querySelector('.toggle-button').addEventListener('click', function() {
            slideout.toggle();
        });
    }
    
    
    $(".thumbnails a").on("click", function (e) {
          e.preventDefault();
          var galleryImage = $(this).attr("href");
          $(".maingreenproducimage").attr("src", galleryImage);
          $(".maingreenproducimagelink").attr("href", galleryImage);
    });
    
    $(".vplist").click(function(e) {
        e.preventDefault();
        var idp= $(this).attr('data-view');
        if(!$(idp).hasClass("active")){
            $(".shoproductrow").removeClass("active");
            $(".productdatarow").removeClass("showp");
        }
        
        $(idp).toggleClass("active");
        $(this).parents(".productdatarow").toggleClass("showp");
    });

    Tipped.create('.simple-tooltip');
    
    $(".producttabs .thetab").click(function(e) {
        e.preventDefault();
        $(this).parents(".producttabs").find(".thetab").removeClass("active");
        $(this).addClass("active");
        var thetabid = $(this).attr("href");
        $(this).parents(".producttabs").find(".tabcontent").removeClass("active");
        $(this).parents(".producttabs").find(thetabid).addClass("active");

    });

    $(".videolist a").click(function(e) {
        e.preventDefault();
        var vid=$(this).attr("vid");
        console.log(vid);
        $(".youtubeframe .embed-container iframe").remove();
        $('<iframe frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>')
            .attr("src", "https://player.vimeo.com/video/" + vid)
            .appendTo(".youtubeframe .embed-container");
    });

   
});

