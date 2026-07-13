import './bootstrap';
// import Alpine from 'alpinejs'
// window.Alpine = Alpine
// Alpine.start()


Alpine.data('qrScanner', () => ({
    cameras: [],
    selectedCameraId: null,
    decodedText: null,
    stream: null,
    scanLoopId: null,
    _starting: false,

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
            if (this.decodedText) return; // stop scanning once matched

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

                if (code && code.data) {
                    this.decodedText = code.data;
                    this.stopCamera();
                    // Hand the decoded PSA ID off to the backend for lookup.
                    this.$wire.lookup(code.data);
                    return;
                }
            }
            this.scanLoopId = requestAnimationFrame(tick);
        };
        this.scanLoopId = requestAnimationFrame(tick);
    },

    scanAgain() {
        this.decodedText = null;
        this.startCamera();
    },
}));