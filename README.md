## WireSpy—a sleek new debug bar for Livewire

<p>
<a href="https://packagist.org/packages/wire-elements/wire-spy"><img src="https://img.shields.io/packagist/dt/wire-elements/wire-spy" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/wire-elements/wire-spy"><img src="https://img.shields.io/packagist/v/wire-elements/wire-spy" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/wire-elements/wire-spy"><img src="https://img.shields.io/packagist/l/wire-elements/wire-spy" alt="License"></a>
</p>

Take your Livewire development to the next level with WireSpy. Instantly debug and interact with your components—inspect their state, modify it on the fly, and even time-travel through state changes to pinpoint issues. Stay on top of every event with a dedicated events page that displays dispatched events, the originating component, and the exact data payload. Plus, with the hot reload feature, any changes to your component files automatically refresh, keeping your workflow fast and efficient. WireSpy provides the power and precision you need to build and debug your Livewire apps.

<p align="center"><img src="/.github/bar.png" alt="WireSpy"></p>

## Installation

Require this package using Composer:

```shell
composer require wire-elements/wire-spy --dev
```

## Usage
Use `CMD+L` or `CTRL+L` on your keyboard to toggle WireSpy.

## Configuration
To change the keybinding, publish the configuration file by running:

```shell
php artisan vendor:publish --tag=wire-spy-config
```

By default, WireSpy is enabled only in your `local` environment. You can override this in `config/wire-spy.php` or by setting an environment variable:

```dotenv
WIRE_SPY_ENABLED=true
```