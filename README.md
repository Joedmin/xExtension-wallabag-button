# FreshRSS Wallabag Button
A [FreshRSS](https://freshrss.org/) extension which adds a [Wallabag](https://wallabag.org/) sharing integration.

With this extension you can simply press the Wallabag Button next to an article or a custom keyboard shortcut to share it with Wallabag. Everything else happens in the background while you can continue reading articles without any further interruptions.

## Download and setup
1. Download the [latest release](https://github.com/Joedmin/xExtension-wallabag-button/releases)
2. Extract and upload it to the `./extensions` folder of your FreshRSS installation
3. Go to `<wallabag_intance_url>/developer` by clicking the `API clients management` menu
4. Create a new API Client with the name of your choice
5. Enter your Wallabag instance url in the Wallabag Button extension settings
6. Enter your `username`, `password`, `client_id` and `client_secret` in the Wallabag Button extension settings
7. Press "Connect to Wallabag"
8. *Optional Set a custom keyboard shortcut*

## Wallabag API Error codes
If you get errors while trying to connect to Wallabag, please check the [Wallabag OpenAPI specification](https://app.wallabag.it/api/doc/).

## Contributing

### Translations
If you'd like to translate the extension to another language please file a pull request. I'd be happy to merge it!

### Development
For local development pull the repository. The prerequisite is [Docker](https://www.docker.com/) installed.

Go to the repository root folder and run `docker compose up` that will start a local [FreshRSS](https://www.freshrss.org/) instance running `http://localhost:8080/` as well as [Wallabag](https://wallabag.org/)  instance running at `http://localhost:8081/`.

Complete it's installation and navigate to Extensions, where you have to enable `Wallabag Button`.

All changes in the PHP files are loaded with each page refresh.

## Credits

This extension is based on [freshrss-pocket-button](https://github.com/christian-putzke/freshrss-pocket-button) (and ) and re-branded for Wallabag.

Thank you very much [Christian Putzke](https://github.com/christian-putzke) for creating the original extension. I used it every day until migrating from Pocket to Wallabag.

Original icon is used from the original [Wallabag repository](https://github.com/wallabag/wallabag) and the outlined version is done using [Online Vector Designing Apps](https://vectordad.com/photo-to-outline/).
