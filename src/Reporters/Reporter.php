<?php

namespace BenchmarkPHP\Reporters;

interface Reporter
{
    public function showHeader(array $data);

    public function showFooter(array $data);

    public function showBlock(array $data);

    public function showSeparator();
}
