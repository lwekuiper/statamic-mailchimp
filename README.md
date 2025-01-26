# Subscribe forms to Mailchimp

This package provides an easy way to integrate Mailchimp with Statamic forms and allows for multi-site configurations.

> **Note**
> This addon requires Statamic Pro to enable multi-site capabilities.

## Features

This addon allows you to:
- Configure Statamic forms to subscribe to a Mailchimp list.
- Supports multi-site capabilities of Statamic Pro.

## Requirements

* PHP 8.2+
* Laravel 10.0+
* Statamic 4.0+

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require lwekuiper/statamic-mailchimp
```

The package will automatically register itself.

## Configuration

Set your Mailchimp API Key and URL in your `.env` file.

```yaml
MAILCHIMP_API_KEY=your-key-here
```

## How to Use

Create your Statamic [forms](https://statamic.dev/forms#content) as usual. Then
