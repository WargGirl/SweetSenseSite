<?php

namespace App\Services;

class Validator
{
    public static function validateEmail(string $email): bool
    {
        return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email) === 1;
    }

    public static function validateStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.{8,}$)(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z\d]).*$/', $password) === 1;
    }

    public static function validateTwitterHandle(string $username): bool
    {
        return preg_match('/^@?[A-Za-z0-9_]{1,15}$/', $username) === 1;
    }

    public static function parseUkrainianLicensePlate(string $plate): ?array
    {
        if (preg_match('/^([A-Za-zА-ЯІЇЄ]{2})\s?(\d{4})\s?([A-Za-zА-ЯІЇЄ]{2})$/u', trim($plate), $matches) !== 1) {
            return null;
        }

        $regionCode = self::normalizePlateLetters($matches[1]);
        $series = self::normalizePlateLetters($matches[3]);
        $regions = self::ukrainianRegions();

        if (!isset($regions[$regionCode]) || !self::isAllowedPlateCode($series)) {
            return null;
        }

        return [
            'number' => $regionCode . ' ' . $matches[2] . ' ' . $series,
            'region_code' => $regionCode,
            'region' => $regions[$regionCode],
        ];
    }

    private static function normalizePlateLetters(string $letters): string
    {
        return strtr(strtoupper($letters), [
            'А' => 'A', 'В' => 'B', 'Е' => 'E', 'І' => 'I',
            'К' => 'K', 'М' => 'M', 'Н' => 'H', 'О' => 'O',
            'Р' => 'P', 'С' => 'C', 'Т' => 'T', 'Х' => 'X',
            'а' => 'A', 'в' => 'B', 'е' => 'E', 'і' => 'I',
            'к' => 'K', 'м' => 'M', 'н' => 'H', 'о' => 'O',
            'р' => 'P', 'с' => 'C', 'т' => 'T', 'х' => 'X',
        ]);
    }

    private static function isAllowedPlateCode(string $letters): bool
    {
        return preg_match('/^[ABEIKMHOPCTX]{2}$/', $letters) === 1;
    }

    private static function ukrainianRegions(): array
    {
        return [
            'AA' => 'м. Київ', 'AB' => 'Вінницька область', 'AC' => 'Волинська область',
            'AE' => 'Дніпропетровська область', 'AH' => 'Донецька область', 'AI' => 'Київська область',
            'AK' => 'Автономна Республіка Крим', 'AM' => 'Житомирська область', 'AO' => 'Закарпатська область',
            'AP' => 'Запорізька область', 'AT' => 'Івано-Франківська область', 'AX' => 'Харківська область',
            'BA' => 'Кіровоградська область', 'BB' => 'Луганська область', 'BC' => 'Львівська область',
            'BE' => 'Миколаївська область', 'BH' => 'Одеська область', 'BI' => 'Полтавська область',
            'BK' => 'Рівненська область', 'BM' => 'Сумська область', 'BO' => 'Тернопільська область',
            'BT' => 'Херсонська область', 'BX' => 'Хмельницька область', 'CA' => 'Черкаська область',
            'CB' => 'Чернігівська область', 'CE' => 'Чернівецька область',
        ];
    }
}