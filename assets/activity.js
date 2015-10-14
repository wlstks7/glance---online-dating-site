$(function(){

    //provide hover function for hovering profile image in recent activity
    $('.hidden_image_img').on({
        mousemove: function(e) {
            $(this).next('img').css({
                top: e.pageY - 260,
                left: e.pageX + 10
            });
        },
        mouseenter: function() {

            //var big = $('<img />', {'class': 'big_img', src: this.src});
            var big = '<div class="hidden_image"><img src="' + this.src + '"></div>';
            $(this).after(big);
        },
        mouseleave: function() {
            $('.hidden_image').remove();
        }
    });
});