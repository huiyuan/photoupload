$(document).ready(function(){
  var clearFields = function(){
    $('input').val('');
  }
	$('.page').hide();
	$('#intro').show();
	$('#upload').click(function(){
		$('button').unbind();
		$('.page').hide();
		$('#content').html('');
		$('#upload_page').show();
		var reader = new FileReader();
		var filter = /^(image\/gif|image\/jpeg|image\/jpg|image\/png)$/i;  
		reader.onload = function (e) {
			var data_string = e.target.result;
			var title_string = $('#title').val();
			var description_string = $('#description').val();
			var token_string = $('#token').val();
			var tags_array = [];
			$.each($('#tags').val().replace(/ /g, '').split(','), function(){tags_array.push(this+'');});
			var args = {title: title_string,
						image: data_string,
						description: description_string,
						tags: tags_array,
						token: token_string};
			$('#content').html('<img src="spinner.gif" alt="loading" />');
			$.ajax({
				url: 'photos.php',
				type: 'POST',
				dataType: 'json',
				data: args,
				error: function(x,y,z){
							console.log(x);
							$('#content').html('Something went wrong with the upload process.');
						},
				success: function(data){
				  clearFields();
					var tag_list = '';
					$.each(data.tags, function(){if(this.length>0){tag_list+= '<li>'+this+'</li>';}});
					tag_list = (tag_list.length>0) ? '<ul>'+tag_list+'</ul>':'';
					var out = '<ul>';
					out += '<li>Title: '+data.title+'</li>';
					out += '<li>Image: <div><img src="'+data.url+'" class="preview" /></div></li>';
					out += '<li>URL: <a href="'+data.url+'">'+data.url+'</a></li>';
					out += '<li> Image ID: '+data.id+'</li>';
					out += '<li>Description: '+data.description+'</li>'
					out += 	'<li>Tags: '+tag_list+'</li>'
					out += '</ul>';
					$('#content').html(out);
				}
			});
		};
		$('#upload_button').click(function(e){
			if ($('#upload_image')[0].files.length === 0) { return; }  
			var file = document.getElementById('upload_image').files[0];  
			if (!filter.test(file.type)) { alert("Image can be png, jpg, or gif."); return; }  
			reader.readAsDataURL(file);
		 	e.preventDefault();
		});
	});
	$('#view_by_id').click(function(){
		$('button').unbind();
		$('.page').hide();
		$('#content').html('');
		$('#view_by_id_page').show();
		$('#view_by_id_button').click(function(){
			$('#content').html('<img src="spinner.gif" alt="loading" />');
			$.ajax({
				url: 'photos.php',
				type: 'GET',
				dataType: 'json',
				data: {id : $('#view_photo_id').val()},
				error: function(x,y,z){
							console.log(x);
							$('#content').html('No photo was found.');
						},
				success: function(data){
				  clearFields();
					var tag_list = '';
					$.each(data.tags, function(){if(this.length>0){tag_list+= '<li>'+this+'</li>';}});
					tag_list = (tag_list.length>0) ? '<ul>'+tag_list+'</ul>':'';
					var out = '<ul>';
					out += 	'<li>Title: '+data.title+'</li>';
					out += '<li>Image: <div><img src="'+data.url+'" class="preview" /></div></li>';
					out += '<li>URL: <a href="'+data.url+'">'+data.url+'</a></li>';
					out += '<li>Image ID: '+data.id+'</li>';
					out += '<li>Description: '+data.description+'</li>'
					out += 	'<li>Tags:'+tag_list+'</li>'
					out += '</ul>';
					$('#content').html(out);
				}
			});
		});
	});
	$('#view_multiple').click(function(){
		$('button').unbind();
		$('.page').hide();
		$('#content').html('');
		$('#view_multiple_page').show();
		$('#view_multiple_button').click(function(){
			$('#content').html('<img src="spinner.gif" alt="loading" />');
			$.ajax({
				url: 'photos.php',
				type: 'GET',
				dataType: 'json',
				data: {start: $('#multiple_image_index').val(),
						count: $('#multiple_count').val()
						},
				error: function(x,y,z){
							$('#content').html('No photo was found.');
						},
				success: function(data){
				  clearFields();
					var out = '<strong>Images: '+data.images.length+' of '+data.count+' total images.</strong>';
					out += '<ul>';
					for(var i = 0; i < data.images.length; i++){
						out += '<li>'
						var tag_list = '';
						$.each(data.images[i].tags, function(){if(this.length>0){tag_list+= '<li>'+this+'</li>';}});
						tag_list = (tag_list.length>0) ? '<ul>'+tag_list+'</ul>':'';
						out += '<ul>';
						out += '<li>Title: '+data.images[i].title+'</li>';
						out += '<li>Image: <div><img src="'+data.images[i].url+'" class="preview" /></div></li>';
						out += '<li>URL:<a href="'+data.images[i].url+'">'+data.images[i].url+'</a></li>';
						out += '<li>Image ID: '+data.images[i].id+'</li>';
						out += '<li>Description: '+data.images[i].description+'</li>'
						out += '<li>Tags: '+tag_list+'</li>'
						out += '</ul>';
						out += '</li>';
					}
				out += '</ul>';
				$('#content').html(out);
				}
			});
		});
	});
	$('#view_multiple_by_tags').click(function(){
		$('button').unbind();
		$('.page').hide();
		$('#content').html('');
		$('#view_multiple_by_tags_page').show();
		$('#view_tags_button').click(function(){
			$('#content').html('<img src="spinner.gif" alt="loading" />');
			var tags_array = [];
			$.each($('#tags_tags').val().replace(/ /g, '').split(','), function(){tags_array.push(this+'');});
			$.ajax({
				url: 'photos-tagged.php',
				type: 'GET',
				dataType: 'json',
				data: {start: $('#tags_image_index').val(),
						count: $('#tags_count').val(),
						tags: tags_array
						},
				error: function(x,y,z){
							$('#content').html('No photo was found.');
						},
				success: function(data){
				  clearFields();
					var out = '<strong>Images: '+data.images.length+' of '+data.count+' matching images.</strong>';
					out += '<ul>';
					for(var i = 0; i < data.images.length; i++){
						out += '<li>'
						var tag_list = '';
						$.each(data.images[i].tags, function(){if(this.length>0){tag_list+= '<li>'+this+'</li>';}});
						tag_list = (tag_list.length>0) ? '<ul>'+tag_list+'</ul>':'';
						out += '<ul>';
						out += '<li>Title: '+data.images[i].title+'</li>';
						out += '<li>Image: <div><img src="'+data.images[i].url+'" class="preview" /></div></li>';
						out += '<li>URL:<a href="'+data.images[i].url+'">'+data.images[i].url+'</a></li>';
						out += '<li>Image ID: '+data.images[i].id+'</li>';
						out += '<li>Description: '+data.images[i].description+'</li>'
						out += '<li>Tags: '+tag_list+'</li>'
						out += '</ul>';
						out += '</li>';
					}
				out += '</ul>';
				$('#content').html(out);
				}
			});
		});	
	});
	$('#delete').click(function(){
		$('button').unbind();
		$('.page').hide();
		$('#content').html('');
		$('#delete_page').show();
		$('#delete_button').click(function(){
			$('#content').html('<img src="spinner.gif" alt="loading" />');
			$.ajax({
				url: 'photos.php',
				type: 'DELETE',
				dataType: 'json',
				data: {id:$('#delete_id').val(), token:$('#delete_token').val()},
				error: function(x,y,z){
              if(x.status === 403){
						    $('#content').html('Deletion token was incorrect.\(The image may not have one\)');
			   }else if(x.status === 404){
							  $('#content').html('Image does not exist.');
               }else if(x.status === 400){
							  $('#content').html('No token given.');
			    }else{
                        $('#content').html('Misc. Error.');
                }
						},
				success: function(data){
						  clearFields();
							$('#content').html('Image deleted.');
				}
			});
		});
	});
});
