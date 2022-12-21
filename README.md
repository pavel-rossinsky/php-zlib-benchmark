# php-zlib-benchmark

# Introduction
zlib is often used in PHP projects for cache compression. Symfony's DeflateMarshaller implementation is an [example](https://github.com/symfony/symfony/blob/bc5fea4e2190f00a207871115a83dd7df03d7637/src/Symfony/Component/Cache/Marshaller/DeflateMarshaller.php). The speed of compression plays an important role in the performance of the web application. Based on this, I decided to check what compression level is the most optimal.

# Benchmark
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

# Conclusion
The results show that high compression levels do not make a big difference in Space Saving but are extremely expensive in terms of CPU time. For example, compression of a 2.4 MB large serialized object with level 9 takes 69.90 ms with a compressed file size of 212 kB. Level 6 (default value) is 37.23 ms with a compressed file size of 217 kB. Compression with level 2 will take 17.59 ms with a compressed file size of 228 kB.
Let's assume that the cache is stored in Redis and we want to take into account the time of transferring an additional amount of information over the network. Transferring of extra 16 kb (228 - 212) through a network with 10 Gbps network bandwidth will take less than 1 ms.

# Running the benchmark script
Clone the repository and run from the root:
```
git clone --recursive --depth=1 https://github.com/pavel-rossinsky/php-zlib-benchmark.git
cd php-zlib-benchmark
php ./src/php_zlib_benchmark.php --file ./samples/sample_2401kb.txt --cycles=100
```
# Contributing
Adding additional benchmarks on other CPU models and architectures is highly appreciated.

# Output of the benchmark script
```
php ./src/php_zlib_benchmark.php --file ./samples/sample_2401kb.txt --cycles=100
Sample length 2401 kB

Compression level 1. Average across 100 tests:
__time_to_deflate_level_1: 16.70 ms
__time_to_inflate_level_1: 6.64 ms
Compressed file size: 292 kB
Space saving: 87.8%
Compression ratio: 8.2
Compression speed: 140.43 MB/s
Decompression speed: 42.97 MB/s
Compression ratio by average time to compress: 492.429 
Compression ratio by average time to decompress: 1238.662 
--------------
Compression level 2. Average across 100 tests:
__time_to_deflate_level_2: 17.23 ms
__time_to_inflate_level_2: 6.54 ms
Compressed file size: 279 kB
Space saving: 88.3%
Compression ratio: 8.6
Compression speed: 136.12 MB/s
Decompression speed: 41.79 MB/s
Compression ratio by average time to compress: 498.164 
Compression ratio by average time to decompress: 1312.447 
--------------
Compression level 3. Average across 100 tests:
__time_to_deflate_level_3: 18.70 ms
__time_to_inflate_level_3: 6.67 ms
Compressed file size: 271 kB
Space saving: 88.7%
Compression ratio: 8.8
Compression speed: 125.38 MB/s
Decompression speed: 39.76 MB/s
Compression ratio by average time to compress: 472.968 
Compression ratio by average time to decompress: 1326.748 
--------------
Compression level 4. Average across 100 tests:
__time_to_deflate_level_4: 24.54 ms
__time_to_inflate_level_4: 6.40 ms
Compressed file size: 244 kB
Space saving: 89.8%
Compression ratio: 9.8
Compression speed: 95.54 MB/s
Decompression speed: 37.34 MB/s
Compression ratio by average time to compress: 399.553 
Compression ratio by average time to decompress: 1531.465 
--------------
Compression level 5. Average across 100 tests:
__time_to_deflate_level_5: 28.25 ms
__time_to_inflate_level_5: 6.40 ms
Compressed file size: 228 kB
Space saving: 90.5%
Compression ratio: 10.5
Compression speed: 82.99 MB/s
Decompression speed: 34.92 MB/s
Compression ratio by average time to compress: 371.205 
Compression ratio by average time to decompress: 1637.958 
--------------
Compression level 6. Average across 100 tests:
__time_to_deflate_level_6: 36.73 ms
__time_to_inflate_level_6: 6.30 ms
Compressed file size: 217 kB
Space saving: 90.9%
Compression ratio: 11.0
Compression speed: 63.84 MB/s
Decompression speed: 33.72 MB/s
Compression ratio by average time to compress: 300.725 
Compression ratio by average time to decompress: 1754.254 
--------------
Compression level 7. Average across 100 tests:
__time_to_deflate_level_7: 42.81 ms
__time_to_inflate_level_7: 6.29 ms
Compressed file size: 215 kB
Space saving: 91.0%
Compression ratio: 11.1
Compression speed: 54.76 MB/s
Decompression speed: 33.50 MB/s
Compression ratio by average time to compress: 260.044 
Compression ratio by average time to decompress: 1771.041 
--------------
Compression level 8. Average across 100 tests:
__time_to_deflate_level_8: 66.37 ms
__time_to_inflate_level_8: 6.28 ms
Compressed file size: 212 kB
Space saving: 91.1%
Compression ratio: 11.3
Compression speed: 35.33 MB/s
Decompression speed: 33.07 MB/s
Compression ratio by average time to compress: 170.150 
Compression ratio by average time to decompress: 1798.682 
--------------
Compression level 9. Average across 100 tests:
__time_to_deflate_level_9: 69.89 ms
__time_to_inflate_level_9: 6.31 ms
Compressed file size: 212 kB
Space saving: 91.2%
Compression ratio: 11.3
Compression speed: 33.55 MB/s
Decompression speed: 32.83 MB/s
Compression ratio by average time to compress: 161.914 
Compression ratio by average time to decompress: 1793.024 
--------------
Level for quickest compression is 1
Level for quickest decompression is 8
Level for most efficient compression (ratio/time) is 2
Level for most efficient decompression (ratio/time) is 8
Compression with level 1 is faster than compression with level 6 by 20.03 ms or 54.5% 
Compression with level 1 is faster than compression with level 9 by 53.20 ms or 76.1% 
Compression with level 2 is faster than compression with level 6 by 19.50 ms or 53.1% 
Compression with level 2 is faster than compression with level 9 by 52.67 ms or 75.4% 
```