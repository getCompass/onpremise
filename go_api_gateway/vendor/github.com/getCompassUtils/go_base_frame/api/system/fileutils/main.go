package fileutils

import (
	"errors"
	"fmt"
	"io"
	"io/ioutil"
	"os"
	"path/filepath"
	"strings"
	"syscall"
)

type File struct {
	path string
}

// cкопировать директорию
func CopyDirectory(srcDir, destDir string) error {

	entries, err := ioutil.ReadDir(srcDir)
	if err != nil {
		return err
	}
	for _, entry := range entries {

		sourcePath := filepath.Join(srcDir, entry.Name())
		destPath := filepath.Join(destDir, entry.Name())

		fileInfo, err := os.Stat(sourcePath)
		if err != nil {
			return err
		}

		stat, ok := fileInfo.Sys().(*syscall.Stat_t)
		if !ok {
			return fmt.Errorf("failed to get raw syscall.Stat_t data for '%s'", sourcePath)
		}

		switch fileInfo.Mode() & os.ModeType {
		case os.ModeDir:
			if err := CreateDirectoryIfNotExists(destPath, 0755); err != nil {
				return err
			}
			if err := CopyDirectory(sourcePath, destPath); err != nil {
				return err
			}
		case os.ModeSymlink:
			if err := CopySymLink(sourcePath, destPath); err != nil {
				return err
			}
		default:
			if err := Copy(sourcePath, destPath); err != nil {
				return err
			}
		}

		if err := os.Lchown(destPath, int(stat.Uid), int(stat.Gid)); err != nil {
			return err
		}

		isSymlink := entry.Mode()&os.ModeSymlink != 0
		if !isSymlink {

			if err := os.Chmod(destPath, entry.Mode()); err != nil {
				return err
			}
		}
	}
	return nil
}

// скопировать файл
func Copy(srcFile string, dstFile string) error {

	out, err := os.Create(dstFile)
	if err != nil {
		return err
	}

	defer out.Close()

	in, err := os.Open(srcFile)
	defer in.Close()
	if err != nil {
		return err
	}

	_, err = io.Copy(out, in)
	if err != nil {
		return err
	}

	return nil
}

// проверить, существует ли файл
func Exists(filePath string) bool {

	if _, err := os.Stat(filePath); os.IsNotExist(err) {
		return false
	}

	return true
}

// создать директорию, если она не существует
func CreateDirectoryIfNotExists(dir string, perm os.FileMode) error {

	if Exists(dir) {
		return nil
	}

	if err := os.MkdirAll(dir, perm); err != nil {
		return fmt.Errorf("failed to create directory: '%s', error: '%s'", dir, err.Error())
	}

	return nil
}

// скопировать символьную ссылку
func CopySymLink(source, dest string) error {

	link, err := os.Readlink(source)
	if err != nil {
		return err
	}
	return os.Symlink(link, dest)
}

// рекурсивный chown
func ChownR(path string, uid, gid int) error {
	return filepath.Walk(path, func(name string, info os.FileInfo, err error) error {
		if err == nil {
			err = os.Chown(name, uid, gid)
		}
		return err
	})
}

// инициализируем file
func Init(workDir, fileSubpath string) (*File, error) {
	nWork, err := NormalizeAbsolutePath(workDir)
	if err != nil {
		return nil, err
	}

	if nWork == string(filepath.Separator) {
		return nil, errors.New("can't write in root dir")
	}

	full := filepath.Join(nWork, fileSubpath)

	nFull, err := NormalizeAbsolutePath(full)
	if err != nil {
		return nil, err
	}

	if !strings.HasPrefix(nFull, nWork) {
		return nil, errors.New("file subpath doesn't belong to work dir")
	}

	return &File{path: nFull}, nil
}

// читаем файл
func (f *File) Read() (string, error) {
	data, err := os.ReadFile(f.path)
	if err != nil {
		return "", err
	}
	return string(data), nil
}

// пишем в файл
func (f *File) Write(content []byte, append bool) error {
	flags := os.O_WRONLY | os.O_CREATE
	if append {
		flags |= os.O_APPEND
	} else {
		flags |= os.O_TRUNC
	}

	file, err := os.OpenFile(f.path, flags, 0644)
	if err != nil {
		return err
	}

	defer file.Close()

	_, err = file.Write(content)
	return err
}

// удаляем файл
func (f *File) Delete() error {
	if Exists(f.path) {
		return os.Remove(f.path)
	}
	return nil
}

// chmod
func (f *File) Chmod(mode os.FileMode) error {
	return os.Chmod(f.path, mode)
}

// получаем путь до файла
func (f *File) GetPath() string {
	return f.path
}

// копируем файл
func (f *File) CopyFileTo(dst *File) error {
	return Copy(f.path, dst.path)
}

// нормализуем абсолютный путь
func NormalizeAbsolutePath(p string) (string, error) {
	if p == "" {
		return "", errors.New("empty path")
	}

	// Только абсолютные пути
	if !filepath.IsAbs(p) {
		return "", errors.New("path must be absolute")
	}

	// filepath.Clean тоже самое что руками удалять "." и ".."
	clean := filepath.Clean(p)

	return clean, nil
}
