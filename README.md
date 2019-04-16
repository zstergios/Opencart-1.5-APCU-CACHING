# Opencart v1.5.x APCU CACHING
Upload the files on your website root folder (like public_html or httpdocs)

This solution will not break your site if your server has not APC installed.
It works with APC or APCU (new version). Also optmizes the file caching, if APC is not supported using optimized way & temp session caching

About VQMOD xml file (works on all opencart versions).
The "engitron.xml" will add the right caching headers to your installation making it NGINIX/VARNISH friendly. For example if a logged user is viewing your website tells the NGINIX/VARNISH not to cache, also when a guest has added a product in the cart.

Also it caching the categories and product functions.
Category::getCategories()
Product::getTotalProducts()
Product::getPopularProducts()

If you wish fast webhosting with APCU / MEMCACHED / NGINIX on CPANEL with MULTIPLE PHP and FREE SSL please visit our website at https://web-expert.gr/en
