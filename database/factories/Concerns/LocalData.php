<?php

namespace Database\Factories\Concerns;

/**
 * Local names, areas, truck numbers and remarks for factories: change the lists here for another city
 */
trait LocalData
{
    protected function personName(?string $gender = null): string
    {
        $male = ['Ramesh', 'Suresh', 'Mahesh', 'Jignesh', 'Hitesh', 'Nilesh', 'Paresh', 'Kalpesh', 'Bhavesh', 'Dipak', 'Vijay', 'Ashok', 'Rajesh', 'Mukesh', 'Haresh', 'Chirag', 'Hardik', 'Parth', 'Kishan', 'Bharat', 'Dhaval', 'Vipul', 'Jayesh', 'Nikunj'];
        $female = ['Meena', 'Hetal', 'Nisha', 'Komal', 'Bhavna', 'Daksha', 'Jagruti', 'Kajal', 'Rekha', 'Hina', 'Pooja', 'Krupa', 'Dimple', 'Varsha', 'Falguni', 'Sonal', 'Payal', 'Jalpa'];
        $surnames = ['Patel', 'Shah', 'Mehta', 'Joshi', 'Desai', 'Trivedi', 'Pandya', 'Vyas', 'Bhatt', 'Parmar', 'Chauhan', 'Solanki', 'Rathod', 'Jadeja', 'Vaghela', 'Gohil', 'Makwana', 'Dave', 'Raval', 'Kotecha', 'Thakkar', 'Sanghvi', 'Pujara', 'Kakkad'];

        $gender ??= fake()->randomElement(['male', 'female']);
        $firstNames = $gender === 'female' ? $female : $male;

        return fake()->randomElement($firstNames).' '.fake()->randomElement($surnames);
    }

    protected function localAddress(): string
    {
        $societies = ['Shanti Nagar', 'Gokul Park', 'Shivam Society', 'Krishna Park', 'Ambica Society', 'Radhe Krishna Society', 'Sardar Nagar', 'Jalaram Society', 'Om Nagar', 'Gayatri Nagar', 'Swaminarayan Park', 'Umiya Society'];
        $areas = $this->localAreas();
        $area = fake()->randomElement(array_keys($areas));

        return fake()->numberBetween(1, 120).', '.fake()->randomElement($societies).', '.$area.', Rajkot - '.$areas[$area];
    }

    /**
     * Area => pincode
     */
    protected function localAreas(): array
    {
        return [
            'Kalawad Road' => '360005', 'University Road' => '360005', 'Raiya Road' => '360007', '150 Feet Ring Road' => '360005',
            'Mavdi' => '360004', 'Kotharia Road' => '360002', 'Gondal Road' => '360002', 'Yagnik Road' => '360001',
            'Bhaktinagar' => '360002', 'Morbi Road' => '360003', 'Jamnagar Road' => '360006', 'Amin Marg' => '360001',
        ];
    }

    /**
     * Realistic remarks (about 60% of records get one)
     */
    protected function bookingRemark(): ?string
    {
        return fake()->boolean(60) ? fake()->randomElement([
            'Call before delivery.',
            'Customer is home after 5 PM.',
            'Deliver to the shop counter.',
            'Second floor, no lift.',
            'Near the temple, ground floor house.',
            'Urgent: cylinder finished.',
            'Collect the empty cylinder on delivery.',
            'Hotel order, deliver before 10 AM.',
            'Customer OK to wait 2 days.',
            'Leave with the watchman if nobody is home.',
        ]) : null;
    }

    protected function purchaseRemark(): ?string
    {
        return fake()->boolean(60) ? fake()->randomElement([
            'Weekly stock from the plant.',
            'Invoice received with the truck.',
            'Truck arrived late from the plant.',
            'Seals checked, all OK.',
            'Unloaded at the main godown.',
            'Driver to bring the invoice copy next trip.',
        ]) : null;
    }

    protected function localTruckNo(): string
    {
        return 'GJ03'.strtoupper(fake()->lexify('??')).fake()->numerify('####');
    }

    /**
     * Obviously fake 10-digit mobile (99999xxxxx), so demo data and screenshots never show a random real person's number
     */
    protected function mobileNumber(): string
    {
        return fake()->numerify('99999#####');
    }
}
