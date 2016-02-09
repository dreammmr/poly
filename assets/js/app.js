var App = function(){
	this.is_mobile = false;
	this.init = function(){
		if(location.pathname == "/" && sessionStorage.getItem('token')) {
			location.href = "makesomenoice";
		}
	};
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
		}
	};
	this.artists = [];
	this.song = {};
	this.current_playlist = [];
	this.songIndex = 0;

};

var app = new App();
app.init();
