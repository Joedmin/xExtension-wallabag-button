# FreshRSS Readeck Button
A [FreshRSS](https://freshrss.org/) extension which adds a better [Readeck](https://readeck.org/en/) sharing integration.

The existing Readeck sharing functionality of FreshRSS always opens a new tab which you have to manually close after sharing. You also have to stay logged in into Readeck.

With this extension you can simply press the Readeck Button next to an article or a custom keyboard shortcut to share it with Readeck. Everything else happens in the background while you can continue reading articles without any further interruptions.

## Download and setup
1. Download the [latest release](https://github.com/christian-putzke/freshrss-readeck-button/releases)
2. Extract and upload it to the `./extensions` folder of your FreshRSS installation
3. Go to `<readeck_intance_url>/profile/tokens`
4. Create a new API token with at least the `Bookmarks : Write Only` permission
5. Enter your Readeck instance url in the Readeck Button extension settings
6. Enter your key in the Readeck Button extension settings
7. Press "Connect to Readeck"
8. *Optional Set a custom keyboard shortcut*

## Readeck API Error codes
If you get errors while trying to connect to Readeck, please check the [Readeck OpenAPI specification](https://codeberg.org/readeck/readeck/src/branch/main/docs/api/api.yaml).

## Contributing

### Translations
If you'd like to translate the extension to another language please file a pull request. I'd be happy to merge it!

### Development
<!-- TODO: pull a config lokalni instance -->

## Credits

This extension is based on [freshrss-pocket-button](https://github.com/christian-putzke/freshrss-pocket-button) and re-branded for Readeck.

Thank you very much [Christian Putzke](https://github.com/christian-putzke) for creating the original extension. I used it every day until migrating from Pocket to Readeck.

<!-- TODO: icon credits -->
