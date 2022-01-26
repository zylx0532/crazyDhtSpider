Supported Extensions
####################

List of the supported extensions
================================

This a list of the supported extensions with class names that this library recognizes.

Converted in PHP 5.6
--------------------

* ``gmp`` (``GMP``)

Converted in PHP 7.2
--------------------

* ``hash`` (``HashContext``)

Converted in PHP 8.0
--------------------

* ``curl`` (``CurlHandle``, ``CurlMultiHandle``, ``CurlShareHandle``)
* ``enchant`` (``EnchantBroker``, ``EnchantDictionary``)
* ``gd`` (``GdImage``),
* ``openssl`` (``OpenSSLAsymmetricKey``, ``OpenSSLCertificate``, ``OpenSSLCertificateSigningRequest``)
* ``shmop`` (``Shmop``)
* ``sockets`` (``AddressInfo``, ``Socket``)
* ``sysvmsg`` (``SysvMessageQueue``)
* ``sysvsem`` (``SysvSemaphore``)
* ``sysvshm`` (``SysvSharedMemory``)
* ``xml`` (``XMLParser``)
* ``xmlrpc`` (``XmlRpcServer``)
* ``xmlwriter`` (``XMLWriter``)
* ``zlib`` (``DeflateContext``, ``InflateContext``)

Converted in PHP 8.1
--------------------

* ``fileinfo`` (``finfo``)
* ``ftp`` (``FTP\Connection``)
* ``imap`` (``IMAP\Connection``)
* ``ldap`` (``LDAP\Connection``, ``LDAP\Result``, ``LDAP\ResultEntry``)
* ``pgsql`` (``PgSql\Connection``, ``PgSql\Result``, ``PgSql\Lob``)

Submitting a missing resource
=============================

.. note::
    Please keep in mind that the minimum supported version is PHP 5.3
    so short array syntax and ``::class`` constants are not available!

The main data file is ``data/object_maps.php``.
It has the following structure:

.. code-block:: php

    <?php

    return array(
        // Top level keys: PHP version where the change was made in the PHP_VERSION_ID form
        70200 => array(
            // Second level keys: extension names as used in extension_loaded() for example
            'hash' => array(
                // Third level:
                // key is a class name after the change
                // value is a resource string before the change
                'HashContext' => 'Hash Context'
            ),
        ),
    );

After you added or fixed the data file, run the generator:

.. code-block:: bash

    php sbin/build_resource_map_class.php

This will update the generated data classes in the ``gen/`` directory.
