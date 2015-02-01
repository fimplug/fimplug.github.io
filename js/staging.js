//Plugin
$(document).ready(function() {
	//Full-height
	
    // Optimalisation: Store the references outside the event handler:
    var $window = $(window);
    var $wrapper = $('.header_wrapper');

    function checkWidth() {
        var windowsize = $window.height();
        $wrapper.css('height', windowsize);
    };
    // Execute on load
    checkWidth();
    // Bind event listener
    $(window).resize(checkWidth);
     
    //Menu-bar
    var scroll_start = 0;
    var startchange = $('#services');
    var offset = startchange.offset();
    if (startchange.length){
        $(document).scroll(function() { 
            scroll_start = $(this).scrollTop();
            if(scroll_start > offset.top) {
                $(".navbar-default").css('background-color', '#222');
            } else {
                $ ('.navbar-default').css('background-color', 'transparent');
            }
        });
    }
    //Dynamic word changer
    var words = new Array('Goeiedag','Mirë dita','Ponchour','Chlomo','Mǐ hăo','Dobrý den','Hej','Goede morgen','Hyvää päivää','Bonjour','Hallo','Ola','Aloha','Shalom','Namaste','Jó napot kívánok','Selamat pagi','Salve','Ciâo','Namaskaram','God dag','Dzień dobry','Buenos días','Tashi delek','Konnichi wa','Hello!');
    var i = 0;
    setInterval( function(){
        $( '#plugin' ).empty().append( words[ i ] );
        if( i < words.length ) {
            i++;
        } else {
            i = 0;
        }
    }, 2000 );
    
    //Scrolling
    // Cache selectors
    var lastId,
        topMenu = $(".navbar-default"),
        topMenuHeight = topMenu.outerHeight()+15,
        // All list items
        menuItems = topMenu.find("a"),
        // Anchors corresponding to menu items
        scrollItems = menuItems.map(function(){
          var item = $($(this).attr("href"));
          if (item.length) { return item; }
        });

    // Bind click handler to menu items
    // so we can get a fancy scroll animation
    menuItems.click(function(e){
      var href = $(this).attr("href"),
          offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
      $('html, body').stop().animate({ 
          scrollTop: offsetTop
      }, 300);
      e.preventDefault();
    });

    // Bind to scroll
    $(window).scroll(function(){
        // Get container scroll position
        var fromTop = $(this).scrollTop()+topMenuHeight;

        // Get id of current scroll item
        var cur = scrollItems.map(function(){
         if ($(this).offset().top < fromTop)
           return this;
        });
        // Get the id of the current element
        cur = cur[cur.length-1];
        var id = cur && cur.length ? cur[0].id : "";

        if (lastId !== id) {
           lastId = id;
           // Set/remove active class
           menuItems
             .parent().removeClass("active")
             .end().filter("[href=#"+id+"]").parent().addClass("active");
        }                   
    });
});