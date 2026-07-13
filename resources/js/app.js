import './bootstrap';
// import Alpine from 'alpinejs'
// window.Alpine = Alpine
// Alpine.start()

// QR SCANNER START
Alpine.data('qrScanner', () => ({
    mode: 'camera',
    cameras: [],
    selectedCameraId: null,
    decodedText: null,
    stream: null,
    scanLoopId: null,
    _starting: false,

    // Upload mode state
    dragging: false,
    decoding: false,
    previewUrl: null,
    uploadError: '',

    async init() {
        await this.loadCameras();
        if (this.selectedCameraId) await this.startCamera();
    },

    async loadCameras() {
        try {
            // Request permission once so device labels are populated
            // (without this, labels come back blank on most browsers).
            const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
            tempStream.getTracks().forEach(t => t.stop());

            const devices = await navigator.mediaDevices.enumerateDevices();
            this.cameras = devices
                .filter(d => d.kind === 'videoinput')
                .map((d, i) => ({
                    deviceId: d.deviceId,
                    label: d.label || `Camera ${i + 1}`,
                }));

            if (this.cameras.length === 0) return;

            const back = this.cameras.find(c => /back|rear|environment/i.test(c.label));
            this.selectedCameraId = (back || this.cameras[0]).deviceId;
        } catch (err) {
            console.error('Could not access camera list:', err);
        }
    },

    async startCamera() {
        if (this.mode !== 'camera') return;
        if (this._starting) return; // avoid overlapping play() calls
        this._starting = true;

        this.stopCamera();

        const videoConstraints = {
            deviceId: this.selectedCameraId ? { exact: this.selectedCameraId } : undefined,
        };

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints });
            this.$refs.video.srcObject = this.stream;
            await this.$refs.video.play();
            this.scanLoop();
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('Camera start failed:', err);
            }
        } finally {
            this._starting = false;
        }
    },

    stopCamera() {
        if (this.scanLoopId) cancelAnimationFrame(this.scanLoopId);
        if (this.stream) {
            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
        }
    },

    switchCamera() {
        this.startCamera();
    },

    scanLoop() {
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });

        const scanIntervalMs = 125;
        let lastScanTime = 0;
        const maxDecodeWidth = 480;

        const tick = (timestamp) => {
            if (this.decodedText || this.mode !== 'camera') return; // stop scanning once matched or mode switched

            if (
                video.readyState === video.HAVE_ENOUGH_DATA &&
                timestamp - lastScanTime >= scanIntervalMs
            ) {
                lastScanTime = timestamp;

                const scale = Math.min(1, maxDecodeWidth / video.videoWidth);
                const w = Math.round(video.videoWidth * scale);
                const h = Math.round(video.videoHeight * scale);

                if (canvas.width !== w || canvas.height !== h) {
                    canvas.width = w;
                    canvas.height = h;
                }

                ctx.drawImage(video, 0, 0, w, h);
                const imageData = ctx.getImageData(0, 0, w, h);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });
            
            // scan delay for 1 sec
           if (code && code.data) {
                    this.stopCamera();
                    this.decoding = true;
                    setTimeout(() => {
                        this.decoding = false;
                        this.decodedText = code.data;
                        this.$wire.lookup(code.data);
                    }, 1000);
                    return;
                }
            }
            this.scanLoopId = requestAnimationFrame(tick);
        };
        this.scanLoopId = requestAnimationFrame(tick);
    },

    scanAgain() {
        this.decodedText = null;
        this.previewUrl = null;
        this.uploadError = '';
        if (this.mode === 'camera') this.startCamera();
    },

    // --- Upload mode ---

    handleFiles(fileList) {
        const file = fileList && fileList[0];
        if (!file) return;

        this.uploadError = '';

        if (!file.type.startsWith('image/')) {
            this.uploadError = 'Please choose an image file.';
            return;
        }

        if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
        this.previewUrl = URL.createObjectURL(file);

        this.decodeImageFile(file);
    },

    decodeImageFile(file) {
        this.decoding = true;

        const img = new Image();
        const url = URL.createObjectURL(file);
        const startedAt = performance.now();
        const minDelayMs = 1000;  //1 sec delay

        const finish = (fn) => {
            const elapsed = performance.now() - startedAt;
            const wait = Math.max(0, minDelayMs - elapsed);
            setTimeout(() => {
                this.decoding = false;
                fn();
            }, wait);
        };

        img.onload = () => {
            try {
                const canvas = this.$refs.uploadCanvas;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                // Cap decode resolution for performance on large photos.
                const maxDim = 1200;
                const scale = Math.min(1, maxDim / Math.max(img.width, img.height));
                const w = Math.round(img.width * scale);
                const h = Math.round(img.height * scale);

                canvas.width = w;
                canvas.height = h;
                ctx.drawImage(img, 0, 0, w, h);

                const imageData = ctx.getImageData(0, 0, w, h);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'attemptBoth',
                });

                URL.revokeObjectURL(url);

                if (code && code.data) {
                    finish(() => {
                        this.decodedText = code.data;
                        this.$wire.lookup(code.data);
                    });
                } else {
                    finish(() => {
                        this.uploadError = 'No QR code detected in that image.';
                    });
                }
            } catch (err) {
                console.error('Image decode failed:', err);
                URL.revokeObjectURL(url);
                finish(() => {
                    this.uploadError = 'Could not read that image.';
                });
            }
        };

        img.onerror = () => {
            URL.revokeObjectURL(url);
            finish(() => {
                this.uploadError = 'Could not load that image.';
            });
        };

        img.src = url;
    },
}));

// QR SCANNER END