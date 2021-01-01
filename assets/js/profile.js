target_list = ['username', 'discord_name', 'mc_name', 'twitch_name'];

var profile_data = {
	username: '',
	discord_name: '',
	mc_name: '',
	twitch_name: ''
};

function load_profile(){
    $.ajax({
        url: 'profile.php',
        type: 'GET',
        data: {action: 'load'},
        dataType: 'json',
        contentType: 'application/json',
        success: function(data){
            if(data['status'] == 'success'){
				profile_data = data;
				for(var target of target_list){
					var element = document.getElementById(target);
					if(element != null){
						console.log(target);
						element.value = data[target];
					}
				}
			}
            else{
                alert('프로필을 로드하는 중 오류가 발생했습니다: ' + data['error']);
            }
        }
    });
}

function save_profile(){
	var input_data = {};
	for(target of target_list){
		var p_input = document.getElementById(target).value;
		if(p_input != profile_data[target])
			input_data[target] = document.getElementById(target).value;
	}
	console.log(input_data);
	
	if(Object.entries(input_data).length !== 0){
		console.log('sex');
		$.ajax({
			url: 'profile.php',
			type: 'GET',
			data: {action: 'save', ...input_data},
			dataType: 'json',
			contentType: 'application/json',
			success: function(data){
				if(data['status'] == 'success'){
					alert('성공적으로 프로필 정보를 저장했습니다.');
				}
				else{
					alert('프로필을 저장하는 중 오류가 발생했습니다: ' + data['error']);
				}
			}
		});
	}
}




