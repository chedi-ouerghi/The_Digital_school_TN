
import React, { useEffect, useRef } from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  Animated,
  Dimensions,
  StyleSheet,
} from 'react-native';
import { luxuryStyles } from '../styles/luxuryStyles';
import { LinearGradient } from 'expo-linear-gradient';

interface Product {
  id: number;
  name: string;
  price: string;
  image: string;
  description: string;
}

interface ProductCardProps {
  product: Product;
  index: number;
  onPress: () => void;
}

const { width } = Dimensions.get('window');
const cardWidth = width / 2 - 20;

const ProductCard: React.FC<ProductCardProps> = ({ product, index, onPress }) => {
  const scaleAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(scaleAnim, {
      toValue: 1,
      duration: 500,
      delay: index * 150,
      useNativeDriver: true,
    }).start();
  }, [index, scaleAnim]);

  const handlePress = () => {
    onPress();
  };

  return (
    <Animated.View style={{ transform: [{ scale: scaleAnim }] }}>
      <TouchableOpacity onPress={handlePress} style={styles.cardContainer}>
        <Image source={{ uri: product.image }} style={styles.productImage} />
        <LinearGradient
          colors={['transparent', 'rgba(0,0,0,0.8)']}
          style={styles.gradient}
        />
        <View style={styles.textContainer}>
          <Text style={styles.productName}>{product.name}</Text>
          <Text style={styles.productPrice}>{product.price}</Text>
        </View>
      </TouchableOpacity>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  cardContainer: {
    width: cardWidth,
    height: cardWidth * 1.5,
    margin: 5,
    borderRadius: 15,
    overflow: 'hidden',
    backgroundColor: '#000',
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 5 },
    shadowOpacity: 0.3,
    shadowRadius: 5,
  },
  productImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  gradient: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    height: '50%',
  },
  textContainer: {
    position: 'absolute',
    bottom: 15,
    left: 15,
    right: 15,
  },
  productName: {
    fontSize: 16,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: '#FFF',
    marginBottom: 5,
  },
  productPrice: {
    fontSize: 14,
    fontFamily: 'Inter_500Medium',
    color: '#FFF',
  },
});

export default ProductCard;
