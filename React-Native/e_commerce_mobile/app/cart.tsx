import React from 'react';
import { View, Text, FlatList, StyleSheet, Image, TouchableOpacity } from 'react-native';
import { useCart } from '../context/CartContext';
import { luxuryStyles, luxuryColors } from '../styles/luxuryStyles';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';

const CartScreen = () => {
  const { cart, removeFromCart, clearCart } = useCart();

  const renderItem = ({ item }) => (
    <View style={styles.itemContainer}>
      <Image source={{ uri: item.image }} style={styles.itemImage} />
      <View style={styles.itemDetails}>
        <Text style={styles.itemName}>{item.name}</Text>
        <Text style={styles.itemPrice}>{item.price}</Text>
        <Text style={styles.itemQuantity}>Quantity: {item.quantity}</Text>
      </View>
      <TouchableOpacity onPress={() => removeFromCart(item.id)} style={styles.removeButton}>
        <Text style={styles.removeButtonText}>Remove</Text>
      </TouchableOpacity>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerText}>Your Cart</Text>
      </View>
      {cart.length === 0 ? (
        <View style={styles.emptyCartContainer}>
          <Text style={styles.emptyCartText}>Your cart is empty.</Text>
          <TouchableOpacity style={styles.shopButton} onPress={() => router.push('/products')}>
            <Text style={styles.shopButtonText}>Shop Now</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <>
          <FlatList
            data={cart}
            renderItem={renderItem}
            keyExtractor={(item) => item.id.toString()}
            contentContainerStyle={styles.listContainer}
          />
          <TouchableOpacity style={styles.clearButton} onPress={clearCart}>
            <Text style={styles.clearButtonText}>Clear Cart</Text>
          </TouchableOpacity>
        </>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: luxuryColors.white,
  },
  header: {
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: luxuryColors.lightGray,
    alignItems: 'center',
  },
  headerText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: luxuryColors.gold,
  },
  listContainer: {
    padding: 20,
  },
  itemContainer: {
    flexDirection: 'row',
    marginBottom: 20,
    backgroundColor: luxuryColors.white,
    borderRadius: 10,
    padding: 10,
    ...luxuryStyles.shadow,
  },
  itemImage: {
    width: 80,
    height: 80,
    borderRadius: 10,
    marginRight: 15,
  },
  itemDetails: {
    flex: 1,
    justifyContent: 'center',
  },
  itemName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: luxuryColors.black,
  },
  itemPrice: {
    fontSize: 16,
    color: luxuryColors.gold,
    marginVertical: 5,
  },
  itemQuantity: {
    fontSize: 14,
    color: luxuryColors.darkGray,
  },
  removeButton: {
    justifyContent: 'center',
    alignItems: 'center',
    padding: 10,
  },
  removeButtonText: {
    color: 'red',
    fontSize: 14,
  },
  emptyCartContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyCartText: {
    fontSize: 18,
    color: luxuryColors.darkGray,
    marginBottom: 20,
  },
  shopButton: {
    backgroundColor: luxuryColors.gold,
    paddingVertical: 15,
    paddingHorizontal: 30,
    borderRadius: 5,
  },
  shopButtonText: {
    color: luxuryColors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
  clearButton: {
    backgroundColor: 'red',
    padding: 15,
    margin: 20,
    borderRadius: 5,
    alignItems: 'center',
  },
  clearButtonText: {
    color: luxuryColors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default CartScreen;
