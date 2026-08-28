<?php
// This file helps PHPStorm understand Laravel facades and global functions
// It suppresses false positive warnings about undefined classes

namespace PHPSTORM_META {
    // Laravel Facades
    override(\Auth::class, map(['' => \Illuminate\Support\Facades\Auth::class]));
    override(\Echo::class, map(['' => \Illuminate\Broadcasting\Broadcasters\Echo::class]));
}
