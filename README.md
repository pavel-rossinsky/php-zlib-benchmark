# php-zlib-benchmark

## Introduction
zlib is often used in PHP projects for cache compression. Symfony's DeflateMarshaller implementation is an [example](https://github.com/symfony/symfony/blob/bc5fea4e2190f00a207871115a83dd7df03d7637/src/Symfony/Component/Cache/Marshaller/DeflateMarshaller.php). The speed of compression plays an important role in the performance of the web application. Based on this, I decided to check what compression level is the most optimal.

## Benchmark
The following results are obtained with the [script](https://github.com/pavel-rossinsky/php-zlib-benchmark/blob/master/src/php_zlib_benchmark.php) benchmarking [gzdeflate](https://www.php.net/manual/en/function.gzdeflate.php) and [gzinflate](https://www.php.net/manual/en/function.gzinflate.php) functions of the [php-zlib](https://www.php.net/manual/en/ref.zlib.php) extension.
The benchmark has been launched with PHP 7.4.33 on Amazon EC2 [c5.xlarge](https://aws.amazon.com/ec2/instance-types/c5/) instance. C5 instances are built on 3.0 GHz Intel Xeon Scalable (Skylake) processors, and have the potential to run at speeds up to 3.5 Ghz using Intel Turbo Boost Technology.

| Compression level | Compression Speed, MB/s | Decompression Speed, MB/s | Ratio | Space Saving, % | Ratio / Time |
|:-----------------:|:-----------------------:|:-------------------------:|:-----:|:---------------:|:------------:|
|         1         |         137.16          |           64.41           |  7.4  |      86.6       |   2246.223   |
|         2         |         131.49          |           63.84           |  7.8  |      87.1       |   2246.826   |
|         3         |         115.43          |           60.84           |  8.0  |      87.5       |   2039.500   |
|         4         |          90.59          |           59.36           |  8.8  |      88.7       |   1764.139   |
|         5         |          79.66          |           58.89           |  9.5  |      89.4       |   1661.567   |
|         6         |          57.36          |           58.92           | 10.0  |      90.0       |   1265.741   |
|         7         |          48.05          |           58.22           | 10.1  |      90.1       |   1069.743   |
|         8         |          29.67          |           57.38           | 10.2  |      90.2       |   669.068    |
|         9         |          27.99          |           56.54           | 10.2  |      90.2       |   631.573    |

The results show that high compression levels do not make a big difference in Space Saving but are extremely expensive in terms of CPU time. For example, compression of a 2.4 MB large serialized object with level 9 takes 69.90 ms. With level 6 (default value): 37.23 ms. Whereas compression with level 2 will take 17.59 ms.
Let's assume that the cache is stored in Redis and we want to take into account the time of transferring an additional amount of information over the network.