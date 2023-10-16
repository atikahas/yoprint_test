<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Upload</title>
</head>
<body>
    <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>

    <h2>Upload History</h2>
    <progress id="uploadProgress" value="0" max="100"></progress>
    <ul>
        @foreach($uploads as $upload)
            <li>{{ $upload->filename }} - Status: {{ $upload->status }} Time: {{ $upload->created_at }} </li>
        @endforeach
    </ul>
    {{-- <script>
        document.getElementById('csv_file').addEventListener('change', function(){
            let maxSize = 48 * 1024 * 1024; // 48MB in bytes
            if(this.files[0].size > maxSize){
                alert('File is too large!');
                this.value = ''; // Clear the input
            }
        });
    </script> --}}
    <script>
        let xhr = new XMLHttpRequest();
        let checkProgressInterval;

        function smoothProgressUpdate(targetValue) {
            const currentValue = parseFloat(uploadProgress.value);
            const increment = (targetValue - currentValue) / 50; // Update 50 times over 5 seconds

            let intervalCount = 0;
            const smoothInterval = setInterval(() => {
                if (intervalCount >= 50) {
                    clearInterval(smoothInterval);
                } else {
                    uploadProgress.value = parseFloat(uploadProgress.value) + increment;
                    intervalCount++;

                    console.log(uploadProgress.value);
                }
            }, 100); // 100ms * 50 = 5 seconds
        }


        function checkProgress(uploadId) {
            fetch(`/upload/progress/${uploadId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.progress === 100) {
                        clearInterval(checkProgressInterval); // Stop checking once we reach 100%
                        alert('Processing complete!');
                    }
                    smoothProgressUpdate(data.progress);
                })
                .catch(error => {
                    console.error('Error checking progress:', error);
                });
        }


        const fileInput = document.querySelector('input[type="file"]');
        const uploadProgress = document.getElementById('uploadProgress');
        const form = document.querySelector('form');

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);

            xhr.upload.addEventListener('progress', function(event) {
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    uploadProgress.value = percentComplete;
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    alert('Upload started!');
                    const uploadId = JSON.parse(xhr.responseText).upload_id;
                    checkProgressInterval = setInterval(() => checkProgress(uploadId), 5000); // Check every 5 seconds
                } else {
                    alert('Upload failed!');
                    console.error('Error response from the server:', xhr.responseText);
                }
            });

            xhr.open('POST', form.action, true);
            xhr.send(formData);
        });



    </script>
</body>
</html>
