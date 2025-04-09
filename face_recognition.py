import face_recognition
import sys
import os

known_faces_dir = "known_faces"  # Folder storing face images of users
input_image_path = sys.argv[1]

# Load input image
try:
    input_image = face_recognition.load_image_file(input_image_path)
    input_encoding = face_recognition.face_encodings(input_image)

    if not input_encoding:
        print("NO_FACE")
        sys.exit(0)
except Exception as e:
    print(f"ERROR: {str(e)}")
    sys.exit(1)

# Compare with stored images
for file in os.listdir(known_faces_dir):
    file_path = os.path.join(known_faces_dir, file)
    try:
        known_image = face_recognition.load_image_file(file_path)
        known_encodings = face_recognition.face_encodings(known_image)

        if not known_encodings:
            print(f"Skipping {file}, no face detected.")
            continue  # Skip images with no detected face

        known_encoding = known_encodings[0]
        matches = face_recognition.compare_faces([known_encoding], input_encoding[0])

        if matches[0]:
            print(file.split(".")[0])  # Print the user ID (assuming filename is the user ID)
            sys.exit(0)
    except Exception as e:
        print(f"ERROR processing {file}: {str(e)}")

print("NO_MATCH")

