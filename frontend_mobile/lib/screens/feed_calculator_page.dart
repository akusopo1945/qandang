import 'package:flutter/material.dart';

class FeedCalculatorPage extends StatefulWidget {
  final double? initialWeight;
  final String? initialPurpose;
  const FeedCalculatorPage({super.key, this.initialWeight, this.initialPurpose});

  @override
  State<FeedCalculatorPage> createState() => _FeedCalculatorPageState();
}

class _FeedCalculatorPageState extends State<FeedCalculatorPage> {
  late TextEditingController _weightController;
  String _purpose = 'fattening'; // fattening or breeding
  String _ageCategory = 'adult'; // kid, young, adult

  double _dryMatter = 0;
  double _greenForage = 0;
  double _concentrate = 0;
  double _waterNeed = 0;

  @override
  void initState() {
    super.initState();
    _weightController = TextEditingController(
      text: widget.initialWeight != null && widget.initialWeight! > 0
          ? widget.initialWeight.toString()
          : '35',
    );
    if (widget.initialPurpose != null) {
      _purpose = widget.initialPurpose!;
    }
    _calculateNutrition();
  }

  @override
  void dispose() {
    _weightController.dispose();
    super.dispose();
  }

  void _calculateNutrition() {
    final weight = double.tryParse(_weightController.text.trim()) ?? 0;
    if (weight <= 0) {
      setState(() {
        _dryMatter = 0;
        _greenForage = 0;
        _concentrate = 0;
        _waterNeed = 0;
      });
      return;
    }

    // Standard Nutrition Calculations:
    // 1. Dry Matter (Bahan Kering / BK) = 3% - 4% of Body Weight
    double bkRatio = _purpose == 'fattening' ? 0.035 : 0.032;
    if (_ageCategory == 'kid') bkRatio = 0.04;

    _dryMatter = weight * bkRatio;

    // 2. Green Forage (Hijauan) = 10% of Fresh Body Weight (or 70-80% of ration)
    _greenForage = weight * 0.10;

    // 3. Concentrate (Konsentrat) = 1% - 2% of Body Weight (or 20-30% of BK)
    double concRatio = _purpose == 'fattening' ? 0.015 : 0.01;
    _concentrate = weight * concRatio;

    // 4. Daily Water Intake = 10% of Body Weight or 3-4 Liters per 10kg
    _waterNeed = weight * 0.10;

    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kalkulator Pakan & Nutrisi'),
        backgroundColor: const Color(0xFF4A6741),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Input Card
            Card(
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: BorderSide(color: Colors.grey.shade200),
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Input Spesifikasi Ternak', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _weightController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Bobot Ternak (kg)',
                        suffixText: 'kg',
                        prefixIcon: const Icon(Icons.monitor_weight_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onChanged: (_) => _calculateNutrition(),
                    ),
                    const SizedBox(height: 16),
                    const Text('Tujuan Pemeliharaan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                    Row(
                      children: [
                        Expanded(
                          child: ChoiceChip(
                            label: const Center(child: Text('Penggemukan')),
                            selected: _purpose == 'fattening',
                            onSelected: (s) {
                              if (s) {
                                setState(() => _purpose = 'fattening');
                                _calculateNutrition();
                              }
                            },
                            selectedColor: const Color(0xFF4A6741),
                            labelStyle: TextStyle(color: _purpose == 'fattening' ? Colors.white : Colors.black),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: ChoiceChip(
                            label: const Center(child: Text('Pembibitan')),
                            selected: _purpose == 'breeding',
                            onSelected: (s) {
                              if (s) {
                                setState(() => _purpose = 'breeding');
                                _calculateNutrition();
                              }
                            },
                            selectedColor: const Color(0xFF4A6741),
                            labelStyle: TextStyle(color: _purpose == 'breeding' ? Colors.white : Colors.black),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    const Text('Kategori Usia', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                    Row(
                      children: [
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Cempe (<6bln)', style: TextStyle(fontSize: 11)),
                            selected: _ageCategory == 'kid',
                            onSelected: (s) {
                              if (s) {
                                setState(() => _ageCategory = 'kid');
                                _calculateNutrition();
                              }
                            },
                            selectedColor: const Color(0xFF4A6741),
                            labelStyle: TextStyle(color: _ageCategory == 'kid' ? Colors.white : Colors.black),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Dara (6-12bln)', style: TextStyle(fontSize: 11)),
                            selected: _ageCategory == 'young',
                            onSelected: (s) {
                              if (s) {
                                setState(() => _ageCategory = 'young');
                                _calculateNutrition();
                              }
                            },
                            selectedColor: const Color(0xFF4A6741),
                            labelStyle: TextStyle(color: _ageCategory == 'young' ? Colors.white : Colors.black),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Dewasa (>1th)', style: TextStyle(fontSize: 11)),
                            selected: _ageCategory == 'adult',
                            onSelected: (s) {
                              if (s) {
                                setState(() => _ageCategory = 'adult');
                                _calculateNutrition();
                              }
                            },
                            selectedColor: const Color(0xFF4A6741),
                            labelStyle: TextStyle(color: _ageCategory == 'adult' ? Colors.white : Colors.black),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),
            const Text('Rekomendasi Pakan Harian (Per Ekor)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            const SizedBox(height: 12),

            // Results Grid
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.3,
              children: [
                _buildResultCard(
                  'Hijauan Segar',
                  '${_greenForage.toStringAsFixed(1)} kg',
                  'Rumput gajah, rambanan, dll',
                  Icons.grass,
                  Colors.green,
                ),
                _buildResultCard(
                  'Konsentrat / Penguat',
                  '${_concentrate.toStringAsFixed(2)} kg',
                  'Dedak, bungkil, ampas tahu',
                  Icons.grain,
                  Colors.amber.shade800,
                ),
                _buildResultCard(
                  'Bahan Kering (BK)',
                  '${_dryMatter.toStringAsFixed(2)} kg',
                  'Kebutuhan nutrisi murni',
                  Icons.pie_chart_outline,
                  Colors.blue,
                ),
                _buildResultCard(
                  'Air Minum',
                  '${_waterNeed.toStringAsFixed(1)} Liter',
                  'Sedia ad-libitum (bebas)',
                  Icons.water_drop_outlined,
                  Colors.cyan,
                ),
              ],
            ),

            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFF4A6741).withOpacity(0.08),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF4A6741).withOpacity(0.2)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.tips_and_updates_outlined, color: Color(0xFF4A6741)),
                  SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Tips: Berikan konsentrat di pagi hari sebelum hijauan agar penyerapan gizi lebih optimal dan mencegah kembung.',
                      style: TextStyle(fontSize: 12, height: 1.4),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard(String title, String value, String desc, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.2)),
        boxShadow: [BoxShadow(color: color.withOpacity(0.05), blurRadius: 6, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(width: 8),
              Expanded(child: Text(title, style: const TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis)),
            ],
          ),
          const SizedBox(height: 8),
          Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 4),
          Text(desc, style: const TextStyle(fontSize: 10, color: Colors.grey), maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }
}
