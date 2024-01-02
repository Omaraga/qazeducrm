let that = $('#modal-form');
$(that).find('#select_group_id').change(function (){
    let id = $(this).find('option:selected').val();
    $.ajax({
        url : '$url',
        type: 'post',
        data: {id : id},
        success: function (data){
            $('#typicalschedule-teacher_id').find('option').each(function (e){
                $(this).remove();
            });
            data = JSON.parse(data);
            console.log('data ', data)
            $(data).each(function (){
                let html = '<option value="' + this.id + '">' + this.fio + '</option>';
                $('#typicalschedule-teacher_id').append(html);
                $('#typicalschedule-teacher_id').removeAttr('disabled');
            });
        }
    })
});