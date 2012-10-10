/**
    @file
    @brief Base JavaScript Library for Imperium
*/

function toNumeric(e,l)
{
    if (!l) {
        l = 4;
    }
    var n = parseFloat(e.value ? e.value : 0);
    if (isNaN(n)) {
        n = 0;
    }
    e.value = parseFloat(n).toFixed(l);
}

function star_step()
{
    // alert(this.getAttribute('data'));
    var star_img = this;
    var star_cur = this.getAttribute('data');
    $.getJSON('/imperium/star?a=next&c=' + star_cur,function(res,ret,xhr) {
        star_img.setAttribute('data',res.name);
        star_img.src = res.src;
    });
}