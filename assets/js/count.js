function handleCountClick(e) {
    const $link = $(e.currentTarget);

    $.ajax({
        url: 'home/count/' + $link.data('direction'),
        method: 'GET'
    }).then(function (response) {
        document.getElementById('js-total-count').innerHTML = response.num;
    });
}

$(document).ready(function () {
    $('.count-link').on('click', handleCountClick);
});


// const $container = $('.js-count');
// $container.find('a').on('click', function (e){
//     e.preventDefault();
//     const $link = $(e.currentTarget)
//
//     $.ajax({
//         url: 'home/count/' + $link.data('direction'),
//         method: 'POST'
//     }).then(function (response){
//         document.getElementById('js-total-count').innerHTML = response.num;
//     })
// })
