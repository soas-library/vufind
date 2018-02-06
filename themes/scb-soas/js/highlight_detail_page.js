$( document ).ready(function() {

var highlight_string = document.getElementById('highlight_detail');

//alert(highlight_string.innerHTML);

var highlight_text = highlight_string.innerHTML;

    $("div.context_highlight").mark(highlight_text, {
    "element": "mark"
    });
    
});