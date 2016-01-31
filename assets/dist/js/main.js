var App = function(){
	this.is_mobile = false;
	this.helpers = {
		clickHandler: function(e){
			var button = e.target;
			while (!button.hasAttribute('data-dialog') && button !== document.body) {
				button = button.parentElement;
			}

			if (!button.hasAttribute('data-dialog')) {
				return;
			}

			var id = button.getAttribute('data-dialog');
			var dialog = document.getElementById(id);
			if (dialog) {
				dialog.open();
			}	
		},
		login: function(){
			var inputs = document.querySelectorAll('#login paper-input'),
				inputs_length = inputs.length,
				is_valid = false;

			for( var i = 0; i < inputs_length; i++ ) {
				is_valid = inputs[i].validate();
				inputs[i].focus();
			}

			if(is_valid) {
				console.log('login');
			}
		}
	};

};

var app = new App();