<?php

$optionsConfig = [
    "file:",
    "cycles:"
];

try {
    $values = getopt(false, $optionsConfig);

    if (empty($values)) {
        throw new Exception('Cannot read the arguments.');
    }

    $file =& $values['file'];

    if (empty($file)) {
        throw new Exception('file option is empty.');
    }
    
    if (!is_readable($file)) {
        throw new Exception(sprintf('The file %s either does not exist or is not readable.', $file));
    }

    $sample = file_get_contents($file);

    if (!$sample) {
        throw new Exception("Can not read the file.");
    }
    
    $testCycles = $values['cycles'] ?? 50;
    
} catch (Throwable $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}

$sampleLengthKb = (int)(strlen($sample)/1024);

echo "Sample length " . $sampleLengthKb . " kB\n\n";

$averageTimeDeflate = [];
$averageTimeInflate = [];
$averageSpaceSaving = [];
$averageCompressRatio = [];

$compressRatioByTimeDeflate = [];
$compressRatioByTimeInflate = [];

for ($level = 1; $level < 10; $level++) {
    for ($j = 0; $j < $testCycles; $j++) {
        $timeDeflate = -microtime(true);
        $compressed = gzdeflate($sample, $level);
        $timeDeflate += microtime(true);

        $averageTimeDeflate[$level] = $averageTimeDeflate[$level] ?? 0.00;
        $averageTimeDeflate[$level] += $timeDeflate; 

        $timeInflate = -microtime(true);
        $decompressedSample = gzinflate($compressed);
        $timeInflate += microtime(true);

        $averageTimeInflate[$level] = $averageTimeInflate[$level] ?? 0.00;
        $averageTimeInflate[$level] += $timeInflate;

        $compressedLengthKb = strlen($compressed)/1024;

        // space saving
        $spaceSaving = 1 - $compressedLengthKb/$sampleLengthKb;

        $averageSpaceSaving[$level] = $averageSpaceSaving[$level] ?? 0.00;
        $averageSpaceSaving[$level] += $spaceSaving;

        // compress ratio
        $compressRatio = $sampleLengthKb/$compressedLengthKb;

        $averageCompressRatio[$level] = $averageCompressRatio[$level] ?? 0.00;
        $averageCompressRatio[$level] += $compressRatio;
    }
    echo sprintf("Compression level %d. Average across %d tests:\n", $level, $testCycles);
    echo sprintf("__time_to_deflate_level_%d: %.2f ms\n",$level, ($averageTimeDeflate[$level]/$testCycles) * 1000);
    echo sprintf("__time_to_inflate_level_%d: %.2f ms\n",$level, ($averageTimeInflate[$level]/$testCycles) * 1000);
    echo sprintf("Compressed file size: %d kB\n", $compressedLengthKb);
    echo sprintf("Space saving: %.1f%%\n", ($averageSpaceSaving[$level]/$testCycles) * 100);
    echo sprintf("Compression ratio: %.1f\n", $averageCompressRatio[$level]/$testCycles);
    
    $compressionSpeed = ($sampleLengthKb/1024) / ($averageTimeDeflate[$level]/$testCycles);
    echo sprintf("Compression speed: %.2f MB/s\n", $compressionSpeed);

    $decompressionSpeed = ($compressedLengthKb/1024) / ($averageTimeInflate[$level]/$testCycles);
    echo sprintf("Decompression speed: %.2f MB/s\n", $decompressionSpeed);
    
    $compressRatioByTimeDeflate[$level] = ($averageCompressRatio[$level]/$testCycles) / ($averageTimeDeflate[$level]/$testCycles);

    echo sprintf("Compression ratio by average time to compress: %.3f \n", $compressRatioByTimeDeflate[$level]);

    $compressRatioByTimeInflate[$level] = ($averageCompressRatio[$level]/$testCycles) / ($averageTimeInflate[$level]/$testCycles);

    echo sprintf("Compression ratio by average time to decompress: %.3f \n", $compressRatioByTimeInflate[$level]);
    
    echo "--------------\n";
}

array_walk($averageTimeDeflate, fn(&$value, $key) => $value=($value/$testCycles)*1000);
asort($averageTimeDeflate);
$fastestCompressionLevel = key($averageTimeDeflate);
echo sprintf("Level for quickest compression is %d\n", key($averageTimeDeflate));
//print_r($averageTimeDeflate);

array_walk($averageTimeInflate, fn(&$value, $key) => $value=($value/$testCycles)*1000);
asort($averageTimeInflate);
echo sprintf("Level for quickest decompression is %d\n", key($averageTimeInflate));
//print_r($averageTimeInflate);

arsort($compressRatioByTimeDeflate);
$mostEfficientCompressionLevel = key($compressRatioByTimeDeflate);
echo sprintf("Level for most efficient compression (ratio/time) is %d\n", $mostEfficientCompressionLevel);
//print_r($compressRatioByTimeDeflate);

arsort($compressRatioByTimeInflate);
echo sprintf("Level for most efficient decompression (ratio/time) is %d\n", key($compressRatioByTimeInflate));
//print_r($compressRatioByTimeInflate);

$standardCompressionLevel = 6;
$maxCompressionLevel = 9;

echo sprintf(
    "Compression with level %d is faster than compression with level %d by %.2f ms or %.1f%% \n",
    $fastestCompressionLevel,
    $standardCompressionLevel,
    $averageTimeDeflate[$standardCompressionLevel] - $averageTimeDeflate[$fastestCompressionLevel],
    (($averageTimeDeflate[$standardCompressionLevel] - $averageTimeDeflate[$fastestCompressionLevel]) / $averageTimeDeflate[$standardCompressionLevel]) * 100
);

echo sprintf(
    "Compression with level %d is faster than compression with level %d by %.2f ms or %.1f%% \n",
    $fastestCompressionLevel,
    $maxCompressionLevel,
    $averageTimeDeflate[$maxCompressionLevel] - $averageTimeDeflate[$fastestCompressionLevel],
    (($averageTimeDeflate[$maxCompressionLevel] - $averageTimeDeflate[$fastestCompressionLevel]) / $averageTimeDeflate[$maxCompressionLevel]) * 100
);

if ($fastestCompressionLevel !== $mostEfficientCompressionLevel) {
    echo sprintf(
        "Compression with level %d is faster than compression with level %d by %.2f ms or %.1f%% \n",
        $mostEfficientCompressionLevel,
        $standardCompressionLevel,
        $averageTimeDeflate[$standardCompressionLevel] - $averageTimeDeflate[$mostEfficientCompressionLevel],
        (($averageTimeDeflate[$standardCompressionLevel] - $averageTimeDeflate[$mostEfficientCompressionLevel]) / $averageTimeDeflate[$standardCompressionLevel]) * 100
    );

    echo sprintf(
        "Compression with level %d is faster than compression with level %d by %.2f ms or %.1f%% \n",
        $mostEfficientCompressionLevel,
        $maxCompressionLevel,
        $averageTimeDeflate[$maxCompressionLevel] - $averageTimeDeflate[$mostEfficientCompressionLevel],
        (($averageTimeDeflate[$maxCompressionLevel] - $averageTimeDeflate[$mostEfficientCompressionLevel]) / $averageTimeDeflate[$maxCompressionLevel]) * 100
    );
}

exit(0);
