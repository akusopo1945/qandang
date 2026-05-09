from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional

app = FastAPI(title="Qandang AI Service", version="1.0.0")

class GrowthRequest(BaseModel):
    current_weight: float
    age_months: int
    breed: str

@app.get("/")
async def root():
    return {"status": "online", "service": "Qandang AI"}

@app.post("/predict/growth")
async def predict_growth(data: GrowthRequest):
    # Placeholder for ML model logic
    predicted_weight = data.current_weight * 1.15 # 15% growth estimation
    return {
        "predicted_weight_next_month": round(predicted_weight, 2),
        "confidence_score": 0.85
    }

@app.get("/health/score/{goat_id}")
async def get_health_score(goat_id: str):
    return {
        "goat_id": goat_id,
        "health_score": 92,
        "status": "Healthy"
    }
