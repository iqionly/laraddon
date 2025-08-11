# Laraddon

Laraddon is a lightweight Laravel package for modular development, inspired by Odoo's modular architecture. It allows developers to organize application features into self-contained "addons" that are automatically loaded at runtime.

> Build scalable Laravel apps with clean module separation, lifecycle management, and dynamic discovery.

---

## 🚀 Features

- 📦 Auto-discovery of modules from `./Addons`
- 🧩 Simple addon structure (routes, views, models, etc.)
- 🔌 No extra config or registration needed
- ⚙️ Designed for extensibility (ServiceProvider support, lifecycle hooks)
- 🎯 Inspired by Odoo’s addon system

---

## 📁 Addon Structure

Each addon lives in the `Addons/` folder and follows this basic structure:
```bash
Addons/
├── YourModule
│ ├── Controllers/
│ ├── Models/
│ ├── Views/
│ ├── init.php
│ ├── manifest.json ← optional metadata (coming soon)
│ └── YourModuleServiceProvider.php (optional)
├── app
├── bootstrap
...
```

---

## ⚙️ How It Works

The package auto-loads all folders under `Addons/` in our code, but you can change the path where do you like.

The `init.php` file is loaded first when the Modules is installed, this can be usefull if you have to create global function or something. The file also can determined if you want change folder path controllers, views, or models.

---

## 🛠 Installation

```bash
composer require iqionly/laraddon
```
And give permission to run execute code. Don't worry, this just create folder `Addons` in your project and composer dump-autoload automatically

---

## 🛣 Roadmap

- [ ] Add addon:create, addon:list, addon:enable Artisan commands
- [ ] Lifecycle support: onInstall, onUpgrade, onUninstall
- [ ] Add caching for faster discovery
- [ ] Metadata module.json support
- [ ] Dependency management

For more information, you can check in here [https://sharing.clickup.com/9018908232/l/6-901805849920-1/list](https://sharing.clickup.com/9018908232/l/6-901805849920-1/list)

---

## 🤝 Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

---

## License

Laraddon is open-sourced software licensed under the MIT license.

---

## 👏 Inspired By

Laravel Modular Community

Odoo Framework

---