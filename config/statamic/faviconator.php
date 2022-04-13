<?php

return [

	/**
	 * For the purpose of actually providing a image file to generate a favicon from, we need some form of a
	 * container. If you set container to null, it will grab the first assets container it finds in Statamic
	 * (usually 'assets', if you haven't changed anything from the template), or else you can set a custom
	 * container.
	 */
	'assets' => [
		'container' => null,
		'path' => 'img/favicons/',
	],

	/**
	 * These are the sizes that will be included in the favicon.ico file. It is recommended, to include at least
	 * 16x16 and 32x32 for minimal support. Most browsers will favor a PNG favicon over the ICO file anyway, but
	 * for the best support, we'll include it. Other options (24x24, 48x48, and 64x64) are commented out and
	 * you can choose yourself, if you want these benefits. Be warned that the file could end up with some size,
	 * if you include them all.
	 */
	'ico_sizes' => [
		16,     // IE9 adress bar, Pinned site jump list/toolbar/overlay
		//			24,     // IE9 pinned site browser UI
		32,     // New tab page in IE, taskbar button in Win7+, Safari Read Later side bar
		//			48,     // Windows site icons
		//			64      // Windows site icons, Safari Reading list sidebar in HiDPI/Retina
	],

	/**
	 * These are the generic favicon sizes, you'll see around the internet. You shouldn't worry too much about
	 * which sizes are included here, because the browser will make a good guesswork which size will fit best,
	 * but if you know what you're doing, than please go ahead, and add as many sizes to the list, as you'd like!
	 */
	'favicon_sizes' => [
		32,
		64,
		96,
		128,
		192,
	],

	/**
	 * These are the sizes for the apple touch icons, which will be used, when you add a shortcut to your website.
	 * 57x57, 76x76 are bit outdated, and just for support of older iPods and iPhones, but the rest is pretty much
	 * what you need for every Apple device out there.
	 */
	'apple_touch_icon_sizes' => [
		57,
		76,
		120,
		144,
		152,
		180,
	],

];
