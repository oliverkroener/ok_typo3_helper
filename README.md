# TYPO3 Helpers

`ok_typo3_helper` is a TYPO3 extension developed by Oliver Kroener that provides general-purpose functionality and utilities to streamline the development of other TYPO3 extensions. It includes features such as a mailer and other reusable components to simplify extension development.

---

## Features

- **Mailer Service**: A robust and configurable mailer for sending emails.
- **Utility Functions**: General-purpose functions to assist with common development tasks.
- **Reusable Components**: Ready-to-use components that can be integrated into your TYPO3 extensions.

---

## Installation

1. Install the extension via composer:
   ```bash
   composer require oliverkroener/ok_typo3_helper
   ```
2. Activate the extension in the TYPO3 backend under **Admin Tools → Extensions**.

---

## Usage

### Mailer Service

```php
use OliverKroener\OkTypo3Helper\Service\MailerService;

// Example of sending an email
$mailer = GeneralUtility::makeInstance(MailerService::class);
$mailer->send(
    'recipient@example.com',
    'Subject Line',
    'This is the email body',
    ['sender@example.com' => 'Sender Name']
);
```

### Utilities

Refer to the [documentation](#documentation) for more examples of the utilities provided by the extension.

---

## Compatibility

- TYPO3 Version: 12.x and above
- PHP Version: 8.1 and above

---

## Development

### Local Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/OliverKroener/ok_typo3_helper.git
   cd ok_typo3_helper
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Set up in your TYPO3 instance as a local extension.

### Testing

Run tests using PHPUnit:
```bash
composer test
```

---

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request with your improvements.

---

## Author

- **Oliver Kroener**  
  [ok@oliver-kroener.de](mailto:ok@oliver-kroener.de)  
  [https://www.oliver-kroener.de](https://www.oliver-kroener.de)

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Documentation

For detailed usage examples and API references, please refer to the [official documentation](https://github.com/OliverKroener/ok_typo3_helper/wiki).

---

Happy Coding!
```

This README reflects the correct `vendor` as `OliverKroener` and the extension name as `ok_typo3_helper`. Let me know if there's anything else you'd like to add!
