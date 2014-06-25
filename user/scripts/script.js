$(function() {
    $('#user-box').find('.label').each(function() {
        var id = $(this).prop('id');
        $('#user-box').find('#' + id).draggable({
            helper: 'clone',
        });

        $('#favorite-box').droppable({
            drop: function(event, ui) {
                var exists = false;
                var span   = this;
                var label  = ui.draggable.clone();
                var id     = ui.draggable.clone().prop('id');
                $('#favorite-box').find('span').each(function() {
                    if ($(this).prop('id') == id) {
                        exists = true;
                    }
                });

                if (exists) {
                    return;
                }

                $.ajax({
                    url      : $('base').prop('href') + 'ajax/favorite_add/',
                    type     : 'POST',
                    dataType : 'json',
                    data     : {
                        user_id           : id.split('-')[1],
                        favorite_group_id : $('.favorite-area').prop('id'),
                    },
                    success  : function(data) {
                        label.html(label.text() + '<button class="batu" id="' + data.id +'">&times;</button>');
                        label.appendTo(span);

                        if (data.status == 'error') {
                            $('#favorite-box').find('#user-' + id).remove();
                            alert('error');
                        }
                    },
                });
            }
        });
    });

    $(document).on('click', '.batu',function() {
        var parent = $(this).parent('span');
        var id     = $(this).prop('id');

        $.ajax({
            url      : $('base').prop('href') + 'ajax/favorite_delete/',
            type     : 'POST',
            dataType : 'json',
            data     : {
                favorite_id : id,
            },
            success  : function(data) {
                if (data.status == 'success') {
                    parent.remove();
                }
            }
        });
    });

    $('#favorite-add').click(function(e) {
        $('#favorite-add-form').lightbox_me({
            centered: true,
        });

        $('#favorite-add-btn').click(function(e) {
            var name   = $('#name').val();
            e.preventDefault();

            if (name == '') {
                alert('');
                return;
            }

            $('#favorite-add-form').find('form').submit();
        });

        e.preventDefault();
    });
});
