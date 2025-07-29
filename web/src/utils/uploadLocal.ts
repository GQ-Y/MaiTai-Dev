/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import SparkMD5 from 'spark-md5'
import { uploadChunk, mergeChunk } from '@/modules/base/api/attachment'

const CHUNK_SIZE = 10 * 1024 * 1024 // 10MB

export async function uploadLocal({ file, onProgress, onSuccess, onError }) {
  if (!file || !(file instanceof File)) {
    console.error('uploadLocal: Invalid file object received', file);
    if (onError) {
      onError({ message: 'Invalid file for upload.' });
    }
    return;
  }

  // 1. 计算文件MD5
  const fileMd5 = await calcFileMd5(file, onProgress)
  const chunkCount = Math.ceil(file.size / CHUNK_SIZE)
  const filename = file.name

  // 2. 分片上传
  for (let i = 0; i < chunkCount; i++) {
    const chunk = file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE)
    const formData = new FormData()
    formData.append('file', chunk)
    formData.append('file_md5', fileMd5)
    formData.append('chunk_index', String(i))
    formData.append('chunk_total', String(chunkCount))
    formData.append('filename', filename)

    await uploadChunk(formData)
    // 上传进度
    onProgress({ percent: Math.round(((i + 1) / chunkCount) * 100) })
  }

  // 3. 合并分片
  const mergeRes = await mergeChunk({
    file_md5: fileMd5,
    chunk_total: chunkCount,
    filename: filename
  })

  // 4. 回调
  if (mergeRes.code === 200) {
    onSuccess(mergeRes)
  } else {
    onError(mergeRes)
  }
}

// 计算文件MD5
function calcFileMd5(file: File, onProgress: (e: any) => void): Promise<string> {
  return new Promise((resolve, reject) => {
    const chunkSize = 2 * 1024 * 1024 // 2MB
    const chunks = Math.ceil(file.size / chunkSize)
    let currentChunk = 0
    const spark = new SparkMD5.ArrayBuffer()
    const fileReader = new FileReader()

    fileReader.onload = function (e) {
      if (!e.target) return reject('文件读取失败')
      spark.append(e.target.result as ArrayBuffer)
      currentChunk++
      // MD5计算进度
      onProgress({ percent: Math.round((currentChunk / chunks) * 100) })
      if (currentChunk < chunks) {
        loadNext()
      } else {
        resolve(spark.end())
      }
    }

    fileReader.onerror = function () {
      reject('文件读取失败')
    }

    function loadNext() {
      const start = currentChunk * chunkSize
      const end = Math.min(file.size, start + chunkSize)
      fileReader.readAsArrayBuffer(file.slice(start, end))
    }

    loadNext()
  })
}
