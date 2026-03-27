import sys  # parancssori argumentumok kezelese
import json  # JSON adatok beolvasása es feldolgozasa
import pandas as pd  # adatkezeles es elemzes
import numpy as np
from sklearn.model_selection import train_test_split  # felosztas tanulo-teszt
from sklearn.preprocessing import StandardScaler  # adatok normalizalasa
import tensorflow as tf
import mysql.connector  # MySQL adatbazis kapcsolas

def load_database_data():
    # db conn
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='temi'
    )

    # minden adatot querybe SQL
    query = "SELECT * FROM data"
    df = pd.read_sql(query, conn)
    conn.close()

    return df

def prepare_model(df):
    # id oszlop kiszedes
    if 'id' in df.columns:
        df = df.drop('id', axis=1)
    # szemelyi szam drop
    if 'szsz' in df.columns:
            df = df.drop('szsz', axis=1)
    # probability columns should be dropped if they exist
    if 'probability' in df.columns:
            df = df.drop('probability', axis=1)

    # eredmeny megkulonboztetese
    X = df.drop('diagnosis', axis=1)
    y = df['diagnosis']

    # felosztas tanulo es tesztadatokra
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    #cross_val_predict

    # numpy -> fuggvenyekbe
    X_train = X_train.to_numpy()
    X_test = X_test.to_numpy()
    y_train = y_train.to_numpy()
    y_test = y_test.to_numpy()

    # AI model definialasa
    model = tf.keras.models.Sequential([
        tf.keras.layers.Dense(256, input_shape=(X_train.shape[1],), activation='sigmoid'),
        tf.keras.layers.Dense(256, activation='sigmoid'),
        tf.keras.layers.Dense(1, activation='sigmoid')
    ])

    # modell forditasa - vesztesegfuggveny meghatarozasa
    model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

    # model betanitasa
    model.fit(X_train, y_train, epochs=10, batch_size=32, validation_data=(X_test, y_test))

    # evaluate - ertekel -  model
    loss, accuracy = model.evaluate(X_test, y_test)
    return model

def main():
    try:
        # JSON input
        input_json = sys.argv[1]
        input_data = json.loads(input_json)

        # adatbazis betoltese es az adatok elokeszitese fenti fuggveny
        db_data = load_database_data()
        model = prepare_model(db_data)

        # Rename input data keys to match expected column names
        if 'concave_points_mean' in input_data:
            input_data['concave points_mean'] = input_data.pop('concave_points_mean')
        if 'concave_points_se' in input_data:
            input_data['concave points_se'] = input_data.pop('concave_points_se')
        if 'concave_points_worst' in input_data:
            input_data['concave points_worst'] = input_data.pop('concave_points_worst')

        # bemeneti dataframe elokeszitese (HTML textboxok, mi hova, mit mivel)
        input_df = pd.DataFrame([input_data], columns=[
            'radius_mean', 'texture_mean', 'perimeter_mean',
            'area_mean', 'smoothness_mean', 'compactness_mean',
            'concavity_mean', 'concave points_mean', 'symmetry_mean',
            'fractal_dimension_mean',
            # SE
            'radius_se', 'texture_se', 'perimeter_se',
            'area_se', 'smoothness_se', 'compactness_se',
            'concavity_se', 'concave points_se', 'symmetry_se',
            'fractal_dimension_se',
            # Worst
            'radius_worst', 'texture_worst', 'perimeter_worst',
            'area_worst', 'smoothness_worst', 'compactness_worst',
            'concavity_worst', 'concave points_worst', 'symmetry_worst',
            'fractal_dimension_worst'
        ])

        # bemenetet numerikussa, vagy hiba
        input_df = input_df.apply(pd.to_numeric, errors='coerce')

        # NULL -> 0
        if input_df.isnull().values.any():
            input_df = input_df.fillna(0)  # Alapértelmezés szerint 0-val töltjük ki

        # convert to numpy array
        input_array = input_df.to_numpy()

        # a predikcio azt adja meg h hany % esejjel rosszindulatu
        prediction = model.predict(input_array)[0][0]

        # ha a predikcio > 50% akk rossz indulatu, ha kisebb akk joindulatu.
        if prediction > 0.5:
            diagnosis = "Rosszindulatu"
        else:
            diagnosis = "Joindulatu"

        # Calculate probability value (confidence in the diagnosis)
        probability = prediction * 100 if prediction > 0.5 else (1 - prediction) * 100

        #kiiras
        print(f"Diagnosztikai vegeredmeny: {diagnosis}")
        print(f"Diagnosztika pontossaga: {probability:.2f}%")
        print(f"Hibalehetoseg: {100-probability:.2f}%")
    except Exception as e:
        print(f"Error: {str(e)}")
        print(f"Diagnosztikai vegeredmeny: Error")
        print(f"Diagnosztika pontossaga: 0.00%")
        print(f"Hibalehetoseg: 100.00%")

if __name__ == "__main__":
    main()