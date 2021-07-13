# Advance example.
```text
'house'                       - Return all 'rooms', 'doors', 'keys', and 'garage' recursive.
'house/keys'                  - Return all 'keys' items.
'house/keys/{id}'             - Return one 'key' item.
'house/rooms/'                - Return all 'rooms' and 'doors' items
'house/rooms/{id}/doors/{id}' - Return one 'door' item.
'house/garage'                - Return information about garage.             
'house/garage/items'          - Return items located at garage. ( Excluded from house )          
```

## Requests & Results:
### Demonstration of the requests and the results:

    
* `GET {BASE_URL}/wp-json/elementor/v1/house/`
```json
{
	"keys": [
		 {
			  "id": 0,
			  "name": "key_0"
		 },
		 {
			  "id": 1,
			  "name": "key_1"
		 }
	],
	"rooms": [
		 {
			  "id": 0,
			  "name": "room_0",
			  "doors": {
					"id": 0,
					"name": "door_0"
			  },
			  "0": {
					"id": 1,
					"name": "door_1"
			  }
		 },
		 {
			  "id": 1,
			  "name": "room_1",
			  "doors": [
					{
						 "id": 0,
						 "name": "door_0"
					},
					{
						 "id": 1,
						 "name": "door_1"
					}
			  ]
		 }
	],
	"garage": {
		 "status": "garage available"
	}
}
```
		  
* `GET {BASE_URL}/wp-json/elementor/v1/house/keys`
```json
[
 {
	  "id": 0,
	  "name": "key_0"
 },
 {
	  "id": 1,
	  "name": "key_1"
 }
]
```
  
* `GET {BASE_URL}/wp-json/elementor/v1/house/keys/1`
```json
{
	"id": 1,
	"name": "key_1"
}
```

* `GET {BASE_URL}/wp-json/elementor/v1/house/rooms`
```json
[
	{
		 "id": 0,
		 "name": "room_0",
		 "doors": {
			  "id": 0,
			  "name": "door_0"
		 },
		 "0": {
			  "id": 1,
			  "name": "door_1"
		 }
	},
	{
		 "id": 1,
		 "name": "room_1",
		 "doors": [
			  {
					"id": 0,
					"name": "door_0"
			  },
			  {
					"id": 1,
					"name": "door_1"
			  }
		 ]
	}
]
```

* `GET {BASE_URL}/wp-json/elementor/v1/house/rooms/1`
```json
{
	"id": 1,
	"name": "room_1",
	"doors": [
		 {
			  "id": 0,
			  "name": "door_0"
		 },
		 {
			  "id": 1,
			  "name": "door_1"
		 }
	]
}
```

* `GET {BASE_URL}/wp-json/elementor/v1/house/rooms/1/doors`
```json
[
	{
		 "id": 0,
		 "name": "door_0"
	},
	{
		 "id": 1,
		 "name": "door_1"
	}
]
```

* `GET {BASE_URL}/wp-json/elementor/v1/house/rooms/1/doors/1`
```json
{
	"id": 1,
	"name": "door_1"
}
```
    
* `GET {BASE_URL}/wp-json/elementor/v1/house/garage`
```json
{
	"status": "garage available"
}
```

*
`GET {BASE_URL}/wp-json/elementor/v1/house/garage/items`
```json
[
	"item0",
	"item1"
]
```

## The full example:
 [https://github.com/iNewLegend/elementor-rest-api-example](https://github.com/iNewLegend/elementor-rest-api-example)

## [Back](controller.md)
