$(function($) {
    $addButton = $('button.add');
    $collection = $addButton.parent().children().first();

    $addButton.click(function () {
        var prototype = $($collection.data('prototype').replace(/__prototype__/g, function() { return (new Date()).getTime(); }));
        prototype.appendTo($collection.children('ul'));
        });

    $('body').on('click', 'button.remove', function () {
        $(this).parent().remove();
    });
});