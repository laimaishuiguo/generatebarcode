<!DOCTYPE html>
<html>

<head>
    <title>Barcode Generator with PDF Export</title>
    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.5/dist/bwip-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: inline-block;
            width: 120px;
        }

        input {
            margin-bottom: 10px;
            padding: 5px;
        }

        button {
            padding: 8px 15px;
            margin-right: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        #savePdfBtn {
            background-color: #2196F3;
        }

        #progressStatus {
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>

<body>
    <h1>Barcode Generator</h1>

    <form id="barcodeForm">
        <label>Starting code:</label>
        <input type="text" name="range_start" id="range_start" required>
        <br>
        <label>Ending code:</label>
        <input type="text" name="range_end" id="range_end" required>
        <br>
        <button type="button" onclick="generateBarcodes()">Generate Barcodes</button>
        <button type="button" id="savePdfBtn" onclick="saveAsPdf()" disabled>Save as PDF (4x8)</button>
    </form>

    <div id="progressStatus"></div>
    <div id="barcodeContainer" style="display: none;">
        <canvas id="barcodeCanvas" width="300" height="100"></canvas>
    </div>

    <script>
        const {
            jsPDF
        } = window.jspdf;
        let generatedBarcodes = [];

        function generateBarcodes() {
            const start = document.getElementById('range_start').value;
            const end = document.getElementById('range_end').value;

            const startNum = parseInt(start);
            const endNum = parseInt(end);

            if (isNaN(startNum) || isNaN(endNum) || startNum > endNum) {
                alert('Please enter valid number range');
                return;
            }

            generatedBarcodes = [];
            for (let i = startNum; i <= endNum; i++) {
                generatedBarcodes.push(i.toString().padStart(6, '0'));
            }

            document.getElementById('savePdfBtn').disabled = false;
            alert(`Ready to generate PDF with ${generatedBarcodes.length} barcodes`);
        }

        function saveAsPdf() {
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'cm',
                format: 'a4'
            });

            const cols = 5;
            const rows = 9;
            const boxWidth = 3.8;
            const boxHeight = 3.0;
            const barcodeWidth = 3.0;
            const barcodeHeight = 0.8;
            const horizontalPadding = (boxWidth - barcodeWidth) / 2;
            const verticalPadding = 0.25;

            const yOffset = 0.6;

            const pageWidth = doc.internal.pageSize.getWidth();
            const marginLeft = (pageWidth - cols * boxWidth) / 2;
            const marginTop = 1.0;

            let currentIndex = 0;
            const total = generatedBarcodes.length;
            const progressDiv = document.getElementById('progressStatus');
            const canvas = document.getElementById('barcodeCanvas');

            function processNextBatch() {
                let batchSize = cols * rows;
                let endIndex = Math.min(currentIndex + batchSize, total);

                if (currentIndex > 0) doc.addPage();

                for (let row = 0; row < rows && currentIndex < endIndex; row++) {
                    for (let col = 0; col < cols && currentIndex < endIndex; col++) {
                        const code = generatedBarcodes[currentIndex];
                        const x = marginLeft + col * boxWidth;
                        const y = marginTop + row * boxHeight;

                        try {
                            bwipjs.toCanvas(canvas, {
                                bcid: 'code128',
                                text: code,
                                scale: 2,
                                height: 8,
                                includetext: false
                            });

                            const imgData = canvas.toDataURL('image/png');

                            doc.setDrawColor(0);
                            doc.setLineWidth(0.05);
                            doc.rect(x, y, boxWidth, boxHeight);

                            // ✅ 学校名称：15号字体
                            doc.setFont('Arial', 'normal');
                            doc.setFontSize(15);
                            doc.text("SJK (C) SU LAI", x + boxWidth / 2, y + verticalPadding + yOffset, {
                                align: 'center'
                            });

                            // 条码图像
                            doc.addImage(
                                imgData,
                                'PNG',
                                x + horizontalPadding,
                                y + verticalPadding + yOffset + 0.4,
                                barcodeWidth,
                                barcodeHeight
                            );

                            // ✅ 条码号码：15号字体
                            doc.setFontSize(15);
                            doc.text(code, x + boxWidth / 2, y + verticalPadding + yOffset + barcodeHeight + 1.0, {
                                align: 'center'
                            });

                        } catch (err) {
                            console.error('Barcode generation error:', err);
                        }

                        currentIndex++;
                        progressDiv.textContent = `Generating barcodes... ${currentIndex} / ${total}`;
                    }
                }

                if (currentIndex < total) {
                    setTimeout(processNextBatch, 30);
                } else {
                    progressDiv.textContent = "Saving PDF...";
                    setTimeout(() => {
                        doc.save('SJK_SU_LAI_Barcodes_5x9.pdf');
                        progressDiv.textContent = "✅ PDF saved successfully.";
                    }, 100);
                }
            }

            progressDiv.textContent = "Generating barcodes...";
            processNextBatch();
        }
    </script>
</body>

</html>