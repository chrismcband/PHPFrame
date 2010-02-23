<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_FilesystemTest extends PHPUnit_Framework_TestCase
{
    private $_sys_tmp_dir;
    
    public function setUp()
    {
        $this->_sys_tmp_dir = PHPFrame_Filesystem::getSystemTempDir();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_cpFileToDir()
    {
        $dir  = $this->_sys_tmp_dir.DS."test-dir";
        $file = $this->_sys_tmp_dir.DS."file1.txt";
        
        if (is_dir($dir)) {
            PHPFrame_Filesystem::rm($dir, true);
        }
        
        if (is_file($file)) {
            unlink($file);
        }
        
        if (!is_dir($dir)) mkdir($dir);
        touch($file);
        
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::cp($file, $dir);
        
        $this->assertTrue(is_file($dir.DS."file1.txt"));
        
        unlink($dir.DS."file1.txt");
        rmdir($dir);
        unlink($file);
    }
    
    public function test_cpFileToFile()
    {
        $source = $this->_sys_tmp_dir.DS."file1.txt";
        $dest   = $this->_sys_tmp_dir.DS."file2.txt";
        
        touch($source);
        
        $this->assertTrue(is_file($source));
        
        PHPFrame_Filesystem::cp($source, $dest);
        
        $this->assertTrue(is_file($dest));
        
        unlink($source);
        unlink($dest);
    }
    
    public function test_cpDirIntoExisitingDir()
    {
        $source = $this->_sys_tmp_dir.DS."test-dir";
        $subdir = $source.DS."subdir";
        $dest   = $this->_sys_tmp_dir.DS."test-dir2";
        
        if (!is_dir($source)) mkdir($source);
        if (!is_dir($subdir)) mkdir($subdir);
        if (!is_dir($dest)) mkdir($dest);
        
        $file1 = $source.DS."file1.txt";
        $file2 = $source.DS."file2.txt";
        $file3 = $source.DS."file3.txt";
        $file4 = $subdir.DS."file4.txt";
        $file5 = $subdir.DS."file5.txt";
        
        touch($file1);
        touch($file2);
        touch($file3);
        touch($file4);
        touch($file5);
        
        $this->assertTrue(is_dir($source));
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_dir($dest));
        $this->assertTrue(is_file($file1));
        $this->assertTrue(is_file($file2));
        $this->assertTrue(is_file($file3));
        $this->assertTrue(is_file($file4));
        $this->assertTrue(is_file($file5));
        
        PHPFrame_Filesystem::cp($source, $dest, true);
        
        $this->assertTrue(is_dir($dest.DS."test-dir".DS."subdir"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."file1.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."file2.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."file3.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."subdir".DS."file4.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."subdir".DS."file5.txt"));
        
        // Cleanup
        PHPFrame_Filesystem::rm($source, true);
        PHPFrame_Filesystem::rm($dest, true);
    }
    
    public function test_cpDirContentsToDir()
    {
        $source = $this->_sys_tmp_dir.DS."test-dir";
        $subdir = $source.DS."subdir";
        $dest   = $this->_sys_tmp_dir.DS."test-dir2";
        
        if (!is_dir($source)) mkdir($source);
        if (!is_dir($subdir)) mkdir($subdir);
        if (!is_dir($dest)) mkdir($dest);
        
        $file1 = $source.DS."file1.txt";
        $file2 = $source.DS."file2.txt";
        $file3 = $source.DS."file3.txt";
        $file4 = $subdir.DS."file4.txt";
        $file5 = $subdir.DS."file5.txt";
        
        touch($file1);
        touch($file2);
        touch($file3);
        touch($file4);
        touch($file5);
        
        $this->assertTrue(is_dir($source));
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_dir($dest));
        $this->assertTrue(is_file($file1));
        $this->assertTrue(is_file($file2));
        $this->assertTrue(is_file($file3));
        $this->assertTrue(is_file($file4));
        $this->assertTrue(is_file($file5));
        
        PHPFrame_Filesystem::cp($source.DS, $dest, true);
        
        $this->assertTrue(is_dir($dest.DS."subdir"));
        $this->assertTrue(is_file($dest.DS."file1.txt"));
        $this->assertTrue(is_file($dest.DS."file2.txt"));
        $this->assertTrue(is_file($dest.DS."file3.txt"));
        $this->assertTrue(is_file($dest.DS."subdir".DS."file4.txt"));
        $this->assertTrue(is_file($dest.DS."subdir".DS."file5.txt"));
        
        // Cleanup
        PHPFrame_Filesystem::rm($source, true);
        PHPFrame_Filesystem::rm($dest, true);
    }
    
    public function test_cpDirToNewDir()
    {
        
    }
    
    public function test_rm()
    {
        $file = $this->_sys_tmp_dir.DS."file1.txt";
        
        touch($file);
        
        $this->assertTrue(is_file($file));
        
        PHPFrame_Filesystem::rm($file);
        
        $this->assertFalse(is_file($file));
    }
    
    public function test_rmRecursive()
    {
        $test_dir = $this->_sys_tmp_dir.DS."test-dir";
        $file1    = $test_dir.DS."file1.txt";
        $file2    = $test_dir.DS."file2.txt";
        $file3    = $test_dir.DS."file3.txt";
        $subdir   = $test_dir.DS."subdir";
        $file4    = $subdir.DS."file4.txt";
        $file5    = $subdir.DS."file5.txt";
        
        if (!is_dir($test_dir)) mkdir($test_dir);
        touch($file1);
        touch($file2);
        touch($file3);
        if (!is_dir($subdir)) mkdir($subdir);
        touch($file4);
        touch($file5);
        
        $this->assertTrue(is_dir($test_dir));
        $this->assertTrue(is_file($file1));
        $this->assertTrue(is_file($file2));
        $this->assertTrue(is_file($file3));
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_file($file4));
        $this->assertTrue(is_file($file5));
        
        PHPFrame_Filesystem::rm($test_dir, true);
        
        $this->assertFalse(is_dir($test_dir));
        $this->assertFalse(is_file($file1));
        $this->assertFalse(is_file($file2));
        $this->assertFalse(is_file($file3));
        $this->assertFalse(is_dir($subdir));
        $this->assertFalse(is_file($file4));
        $this->assertFalse(is_file($file5));
    }
    
    public function test_ensureWritableDir()
    {
        $dir    = $this->_sys_tmp_dir.DS."test-dir";
        $subdir = $dir.DS."subdir";
        
        PHPFrame_Filesystem::ensureWritableDir($subdir);
        
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_writable($subdir));
        
        PHPFrame_Filesystem::rm($dir, true);
    }
    
    public function test_isEmptyDir()
    {
        $dir = $this->_sys_tmp_dir.DS."test-dir";
        
        if (is_dir($dir)) PHPFrame_Filesystem::rm($dir, true);

        mkdir($dir);
        
        $this->assertTrue(PHPFrame_Filesystem::isEmptyDir($dir));
        
        touch($dir.DS."file1.txt");
        touch($dir.DS."file2.txt");
        
        $this->assertFalse(PHPFrame_Filesystem::isEmptyDir($dir));
        
        PHPFrame_Filesystem::rm($dir, true);
    }
    
    public function test_uploadFile()
    {
        
    }
    
    public function test_getSystemTempDir()
    {
        $this->assertTrue(is_dir(PHPFrame_Filesystem::getSystemTempDir()));
        $this->assertTrue(is_writable(PHPFrame_Filesystem::getSystemTempDir()));
    }
}
