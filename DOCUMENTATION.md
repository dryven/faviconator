# Documentation

Thank you for using our addon *Faviconator*! Here you will find help on how to install and use the addon.

## Installation

1. There are two ways to install Cookie Byte on your site

    a. Go to the ``Tools > Addons`` panel in the Control Panel, search for ``Faviconator``, click on the result card and hit **Install**

    b. In the console, type in the command ``composer require dryven/faviconator`` and hit **Enter**

2. After the installation is completed, you should put the tag ``{{ faviconator }}`` somewhere in the ``<head>`` of your layout, where you'd like to put the favicon links


## Usage

![Faviconator Navigation Item](https://raw.githubusercontent.com/dryven/faviconator/main/repo/FaviconatorNavItem.png)

Before the addon does anything, you have to set a favicon image. For that go to the Control Panel and you will see that the installation added a new navigation item to the navigation list: **Faviconator**! Click on it and then you will get to the following screen.

![Faviconator Settings](https://raw.githubusercontent.com/dryven/faviconator/main/repo/FaviconatorCPSettings.png)

In the above screenshot you can see that we have already filled the settings with our dryven logo, a optional silhouette and a our branding color as the theme color. Mind that you can only use a PNG for a favicon and the size should be at least 192 x 192 pixels for quality. And that's all you need to do for using the whole power of the Faviconator addon!

As soon as you'll click on the **Save**-Button, the provided favicon image will be converted to all the different formats, that are useful for the many browsers that are out there. In particular, it will generate a ``favicon.ico`` and all icon sizes used for the Apple and Android homescreen.

## Pro Usage

There are some options on top of the standard usage, to give you more control over what images are generated. For that you can publish the addon's config by using the following command. Inside of the config file you will find long comments on what each option will do.

```
php artisan vendor:publish --tag=faviconator-config
```

By the way, you can generate the favicons by using the following command as soon as they have been set in the Control Panel.

```
php artisan favicon:generate
```