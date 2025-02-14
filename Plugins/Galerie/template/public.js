
document.addEventListener("DOMContentLoaded", function() {

    if (document.querySelector('.galerie .categories button')) {

        document.querySelectorAll('.galerie .categories button').forEach(function(item, index) {
            item.addEventListener('click', function(){
                var rel = this.getAttribute('rel');

                document.querySelectorAll('.galerie #list li').forEach(function(item, index) {
                    item.style.display = 'none';
                });

                document.querySelectorAll('.galerie #list li.'+rel).forEach(function(item, index) {
                    fadeIn(item, 'block');
                });

            });
        });

    }

});