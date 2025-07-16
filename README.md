# CssPurger

**CssPurger** is a lightweight PHP library to remove unused CSS rules from large stylesheets. It’s perfect for optimizing CSS frameworks like Bootstrap based on actual usage in your project.

---

## 🧩 Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require jbsnewmedia/css-purger
```

---

## 🚀 Features

* Removes unused CSS based on custom-defined selectors
* Supports nested blocks (e.g. `@media`)
* Fully supports selectors with pseudo-classes like `:hover`, `:focus`, etc.
* Output as **minified** or **pretty-printed** CSS
* Easily extendable via subclassing
* No external dependencies
* Bootstrap integration example included

---

## 🔧 Usage

```php
use JBSNewMedia\CssPurger\CssPurgerBootstrap;

$purger = new CssPurgerBootstrap('./assets/css/bootstrap.css');

$purger->loadContent();
$purger->prepareContent();
$purger->runContent();

// Add the selectors you want to keep
$purger->addSelectors([
    ':root',
    '[data-bs-theme=light]',
    '[data-bs-theme=dark]',
    'body',
    'h1',
    '.h1',
    '.container',
    '.pt-3',
    '.pb-3',
    '.alert',
    '.alert-danger',
    '.btn:hover',
]);

// Save the result
file_put_contents('./assets/css/bootstrap-purged.css', $purger->generateOutput(false)); // readable
file_put_contents('./assets/css/bootstrap-purged.min.css', $purger->generateOutput());   // minified
```

---

## 🧠 Extendability

You can subclass `CssPurger` to customize parsing or handling, e.g. for Bootstrap-specific structures:

```php
use JBSNewMedia\CssPurger\CssPurger;

class CssPurgerBootstrap extends CssPurger
{
    public function prepareContent(): self
    {
        $this->cssBlockPrefix = substr($this->content, 0, strpos($this->content, ':root'));
        $this->content = str_replace("*/\n:root,", "*/\n}\n:root,", $this->content);
        return $this;
    }
}
```

---

## ⚠️ Notes

* This is not a full CSS parser — it uses lightweight string-based logic for performance
* You must explicitly define which selectors to keep

---

## 📄 License

MIT License — free for personal and commercial use.

---

## 🤝 Contributing

Pull requests, bug reports and ideas for improvements are always welcome!

---

## 🧑‍💻 Author

Maintained by [JBS New Media](https://github.com/jbsnewmedia).