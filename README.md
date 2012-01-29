GeoPhone
========

PHP class for offline Geolocating a US based phone number
---------------------------------------------------------

Geocoding of US phone numbers based on [libphonenumber](http://code.google.com/p/libphonenumber/source/browse/trunk/resources/geocoding/en/1.txt)
Source uses the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0)
Make sure your cache directory is writable if you want to regenerate storage objects

Usage
-----
```php
$location = GeoPhone::find('+14089961010'); // returns California
```

Benchmarks
----------
* Storage Generation (no search) 0.08574104309082 seconds
* Search (no generation) 0.063844919204712 seconds
* Storage + Search 0.14285802841187 seconds
