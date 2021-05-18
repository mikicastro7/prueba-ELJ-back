<?php

namespace App\Http\Controllers;
use Validator;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class ImageColorController extends Controller
{

    public function mostUsedColor(Request $request) {
        $validator = Validator::make($request->all(),
        [
            'file' => 'required|mimes:jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }

        $comparisonColorsHex = ["Aqua" => "#00FFFF", "Black" => "#000000", "Blue" => "#0000FF", "Fuchsia" => "FF00FF",
        "Navy" => "#000080", "Olive" => "#808000", "Purple" => "#800080", "Red" => "#FF0000", "Gray" => "#808080", "Green" => "#008000",
        "Lime" => "#00ff00", "Maroon" => "#800000", "Silver" => "#C0C0C0", "Teal" => "#008080", "White" => "#FFFFFF", "Yellow" => "#FFFF00"];

        $similarColorResult = [
            "colorName" => "",
            "colorHex" => "",
        ];

        $palette = Palette::fromFilename($request->file('file'));

        $extractor = new ColorExtractor($palette);
        $mostCommonColor = $extractor->extract(1);

        $mostCommonColorRGB = Color::fromIntToRgb($mostCommonColor[0]);

        foreach ($comparisonColorsHex as $colorName => $comparisonColorHex) {
            $colorRgb = Color::fromIntToRgb(Color::fromHexToInt($comparisonColorHex));
            $diffColors = sqrt(($mostCommonColorRGB["r"] - $colorRgb["r"])**2 + ($mostCommonColorRGB["g"] - $colorRgb["g"])**2 + ($mostCommonColorRGB["b"] - $colorRgb["b"])**2);

            if(!isset($similarColorResult["minDif"]) || $similarColorResult["minDif"] > $diffColors){
                $similarColorResult["minDif"] = $diffColors;
                $similarColorResult["colorName"] = $colorName;
                $similarColorResult["colorHex"] = $comparisonColorHex;
            }
        }

        return response()->json([
            'colorName' => $similarColorResult["colorName"],
            'colorHex' => $similarColorResult["colorHex"],
        ]);
    }
}
