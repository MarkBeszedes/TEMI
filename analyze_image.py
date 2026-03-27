import sys
import os
import json
import numpy as np
import tensorflow as tf
from tensorflow.keras.preprocessing import image
from tensorflow.keras.applications.mobilenet_v2 import MobileNetV2, preprocess_input
from tensorflow.keras.models import Sequential, load_model
from tensorflow.keras.layers import Dense, GlobalAveragePooling2D
import cv2

# tf warnings
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'  # 0=all, 1=info, 2=warning, 3=error
tf.compat.v1.logging.set_verbosity(tf.compat.v1.logging.ERROR)

def analyze_tumor_image(image_path):
    try:        
        # check if model exists, if not create and train it
        model_path = 'tumor_image_model.h5'
        if os.path.exists(model_path):
            model = load_model(model_path)
        else:
            # create model: transfer learning, MobileNetV2
            base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(224, 224, 3))
            # base model
            base_model.trainable = False
            # create new model on top
            model = Sequential([
                base_model,
                GlobalAveragePooling2D(),
                Dense(256, activation='relu'),
                Dense(1, activation='sigmoid')
            ])
            # compile model
            model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])
            model.save(model_path)

        # Check if file exists
        if not os.path.exists(image_path):
            return {'error': f'Image file not found at {image_path}'}

        # load and preprocess the image
        img = cv2.imread(image_path)
        if img is None:
            return {'error': f'Failed to load image at {image_path}'}

        # resize image
        img = cv2.resize(img, (224, 224))

        # convert to RGB if not
        if len(img.shape) == 2:  # Grayscale
            img = cv2.cvtColor(img, cv2.COLOR_GRAY2RGB)
        elif img.shape[2] == 1:  # Grayscale with channel
            img = cv2.cvtColor(img, cv2.COLOR_GRAY2RGB)
        elif img.shape[2] == 4:  # RGBA
            img = cv2.cvtColor(img, cv2.COLOR_RGBA2RGB)

        # preprocess image
        img = img.astype(np.float32)
        img = preprocess_input(img)
        img = np.expand_dims(img, axis=0)

        # prediction
        prediction = model.predict(img)[0][0]

        # diagnosis and probability
        diagnosis = 1 if prediction > 0.5 else 0
        probability = float(prediction * 100 if prediction > 0.5 else (1 - prediction) * 100)

        # Return properly formatted JSON result
        result = {
            'diagnosis': diagnosis,
            'probability': probability
        }

        # Write result to a log file for debugging
        with open('analyze_log.txt', 'a') as log_file:
            log_file.write(f"Analyzed {image_path}: {json.dumps(result)}\n")

        return result

    except Exception as e:
        # Log the error
        with open('analyze_error_log.txt', 'a') as log_file:
            log_file.write(f"Error analyzing {image_path}: {str(e)}\n")
        return {'error': f'Analysis error: {str(e)}'}

if __name__ == "__main__":
    try:
        if len(sys.argv) != 2:
            error_msg = {'error': 'Invalid arguments. Usage: python analyze_image.py [image_path]'}
            print(json.dumps(error_msg))
            with open('analyze_error_log.txt', 'a') as log_file:
                log_file.write(f"Invalid arguments: {sys.argv}\n")
            sys.exit(1)

        # Get image path from arguments
        image_path = sys.argv[1]

        # Write to log that script is running
        with open('analyze_log.txt', 'a') as log_file:
            log_file.write(f"Starting analysis of {image_path}\n")

        # Run analysis
        result = analyze_tumor_image(image_path)

        # Ensure the result is properly formatted
        print(json.dumps(result))
        sys.stdout.flush()  # Ensure output is flushed to PHP

    except Exception as e:
        error_msg = {'error': f'Unexpected error: {str(e)}'}
        print(json.dumps(error_msg))
        with open('analyze_error_log.txt', 'a') as log_file:
            log_file.write(f"Unexpected error: {str(e)}\n")
        sys.exit(1)