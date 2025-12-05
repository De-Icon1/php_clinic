<!DOCTYPE html>
<html>
<head>
  <title>Take a Picture</title>
</head>
<body>
  <h2>Webcam Preview</h2>
  <video id="video" width="640" height="480" autoplay></video>
  <button id="snap">Take Picture</button>
  <h2>Captured Image</h2>
  <canvas id="canvas" width="640" height="480"></canvas>

  <script>
    // Grab elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const snapButton = document.getElementById('snap');

    // Request access to webcam
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => {
        video.srcObject = stream;
      })
      .catch(err => {
        console.error("Error accessing webcam:", err);
      });

    // Capture a frame from the video
    snapButton.addEventListener('click', () => {
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
    });
  </script>
</body>
</html>