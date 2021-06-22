$( document ).ready(function() {

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

    Tipped.create('.simple-tooltip');

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

