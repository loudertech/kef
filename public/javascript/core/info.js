
/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Tag
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: info.js,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

var TwitterEcho = {

	blinker: null,

	showBlinker: function(){
		TwitterEcho.blinker = window.setInterval(function(){
			var loading = $('loading');
			if(loading.getOpacity()==1.0){
				new Effect.Opacity(loading, {
					to: 0.5
				})
			} else {
				new Effect.Opacity(loading, {
					to: 1.0
				})
			}
		}, 1200);
	},

	initialize: function(){
		TwitterEcho.showBlinker();
		var louderTweets = $('louder-tweets');
		louderTweets.src = "http://www.loudertechnology.com/site/blog/getTwitts?external=1";
		louderTweets.observe('load', function(){
			$('loading').hide();
			window.clearInterval(TwitterEcho.blinker);
			this.show();
		})
	}

};

var GettingStarted = {

	show: function(){
		new Effect.Move('table-content', {
			x: -1024
		})
	}

}

new Event.observe(document, 'dom:loaded', TwitterEcho.initialize);
