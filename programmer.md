
# Programmer Notes

Below are hints to developer maintaining this package

## Compile js and css

To (re)compile the css and js assets published by this package execute:

```shell
npx mix
```
This output of compilation will be placed into the package's local public/vendor/jlab-epas
hierarchy.  When published, these files will be published into the Laravel application's
public/ path when the artisan publish command is invoked.

```shell
php artisan vendor:publish --tag=jlab-epas-assets --force
```




