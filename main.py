from fastapi import FastAPI
from pydantic import BaseModel
import math
import os

app = FastAPI()

# Buat kerangka data sesuai yang dikirim Laravel
class Koordinat(BaseModel):
    latitude: float   # Diubah jadi 'latitude' biar pas sama kiriman Laravel
    longitude: float  # Diubah jadi 'longitude' biar pas sama kiriman Laravel

# Koordinat Toko/Bengkel ElektronikCare Lu
LAT_TOKO = -6.30000
LON_TOKO = 107.30000

@app.post("/api/hitung-jarak")
def hitung_jarak(data: Koordinat):
    # Rumus Haversine untuk hitung jarak otomatis
    R = 6371.0  # Jari-jari bumi dalam kilometer

    lat1 = math.radians(LAT_TOKO)
    lon1 = math.radians(LON_TOKO)
    lat2 = math.radians(data.latitude)
    lon2 = math.radians(data.longitude)

    dlon = lon2 - lon1
    dlat = lat2 - lat1

    a = math.sin(dlat / 2)**2 + math.cos(lat1) * math.cos(lat2) * math.sin(dlon / 2)**2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))
    
    jarak = R * c

    # SINKRONISASI: Diubah menjadi 'distance_km' sesuai kebutuhan Laravel lu kemarin, Mad!
    return {"distance_km": round(jarak, 2)}

# TAMBAHAN WAJIB BIA BISA ONLINE DI CLOUD SERVER
if __name__ == "__main__":
    import uvicorn
    # Membaca port dinamis dari cloud hosting (Render/Railway)
    port = int(os.environ.get("PORT", 8000)) 
    uvicorn.run(app, host="0.0.0.0", port=port)